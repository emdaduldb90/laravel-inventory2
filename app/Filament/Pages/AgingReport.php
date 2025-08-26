<?php

namespace App\Filament\Pages;

use Carbon\Carbon;
use Filament\Actions;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Pages\Page;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class AgingReport extends Page implements Forms\Contracts\HasForms
{
    use Forms\Concerns\InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-document-chart-bar';
    protected static ?string $navigationLabel = 'AR/AP Aging';
    protected static ?string $navigationGroup = 'Reports';
    protected static string $view = 'filament.pages.aging-report';

    /** @var array<string,mixed> */
    public array $filters = [];

    /** @var array<int,array<string,mixed>> */
    public array $rows = [];

    public function mount(): void
    {
        $this->form->fill([
            'type' => 'ar',                    // ar = Receivables, ap = Payables
            'as_of' => now()->toDateString(),  // reporting date
            'b1' => 30, 'b2' => 60, 'b3' => 90,
        ]);

        $this->loadData();
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Grid::make(6)->schema([
                    Forms\Components\Select::make('type')
                        ->label('Report')
                        ->options([
                            'ar' => 'Accounts Receivable (AR)',
                            'ap' => 'Accounts Payable (AP)',
                        ])
                        ->required()
                        ->reactive()
                        ->afterStateUpdated(fn () => $this->loadData())
                        ->columnSpan(2),

                    Forms\Components\DatePicker::make('as_of')
                        ->label('As of')
                        ->required()
                        ->reactive()
                        ->afterStateUpdated(fn () => $this->loadData())
                        ->columnSpan(2),

                    Forms\Components\TextInput::make('b1')->numeric()->label('Bucket 1')->default(30)
                        ->helperText('e.g. 30')
                        ->reactive()->afterStateUpdated(fn () => $this->loadData()),
                    Forms\Components\TextInput::make('b2')->numeric()->label('Bucket 2')->default(60)
                        ->helperText('e.g. 60')
                        ->reactive()->afterStateUpdated(fn () => $this->loadData()),
                    Forms\Components\TextInput::make('b3')->numeric()->label('Bucket 3')->default(90)
                        ->helperText('e.g. 90')
                        ->reactive()->afterStateUpdated(fn () => $this->loadData()),
                ]),
            ])
            ->statePath('filters');
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('refresh')
                ->label('Refresh')
                ->icon('heroicon-o-arrow-path')
                ->action(fn () => $this->loadData()),
            Actions\Action::make('export')
                ->label('Export CSV')
                ->icon('heroicon-o-arrow-down-tray')
                ->action(function () {
                    $csv = $this->makeCsv($this->rows, $this->bucketLabels());
                    return response()->streamDownload(function () use ($csv) {
                        echo $csv;
                    }, 'aging-' . $this->filters['type'] . '-' . now()->format('YmdHis') . '.csv');
                }),
        ];
    }

    /** @return array{current:string,b1:string,b2:string,b3:string} */
    public function bucketLabels(): array
    {
        $b1 = (int)($this->filters['b1'] ?? 30);
        $b2 = (int)($this->filters['b2'] ?? 60);
        $b3 = (int)($this->filters['b3'] ?? 90);

        return [
            'current' => "0–{$b1}",
            'b1'      => ($b1 + 1) . "–{$b2}",
            'b2'      => ($b2 + 1) . "–{$b3}",
            'b3'      => ">{$b3}",
        ];
    }

    /** মূল কুয়েরি চালিয়ে rows ফিল্ডে ডাটা বসায় */
    public function loadData(): void
    {
        $companyId = auth()->user()->company_id;
        $type   = $this->filters['type'] ?? 'ar';
        $asOf   = Carbon::parse($this->filters['as_of'] ?? now())->toDateString();
        $b1     = (int)($this->filters['b1'] ?? 30);
        $b2     = (int)($this->filters['b2'] ?? 60);
        $b3     = (int)($this->filters['b3'] ?? 90);

        if ($type === 'ar') {
            $diff = "DATEDIFF('{$asOf}', s.issue_date)";
            $rows = DB::table('sales as s')
                ->join('customers as c', 'c.id', '=', 's.customer_id')
                ->selectRaw("
                    s.customer_id as id,
                    c.name as name,
                    SUM(CASE WHEN {$diff} <= {$b1} THEN s.due ELSE 0 END) as current_bucket,
                    SUM(CASE WHEN {$diff} >  {$b1} AND {$diff} <= {$b2} THEN s.due ELSE 0 END) as bucket1,
                    SUM(CASE WHEN {$diff} >  {$b2} AND {$diff} <= {$b3} THEN s.due ELSE 0 END) as bucket2,
                    SUM(CASE WHEN {$diff} >  {$b3} THEN s.due ELSE 0 END) as bucket3,
                    SUM(s.due) as total
                ")
                ->where('s.company_id', $companyId)
                ->where('s.due', '>', 0)
                ->groupBy('s.customer_id', 'c.name')
                ->orderBy('c.name')
                ->get();
        } else {
            $diff = "DATEDIFF('{$asOf}', p.order_date)";
            $rows = DB::table('purchases as p')
                ->join('suppliers as s2', 's2.id', '=', 'p.supplier_id')
                ->selectRaw("
                    p.supplier_id as id,
                    s2.name as name,
                    SUM(CASE WHEN {$diff} <= {$b1} THEN p.due ELSE 0 END) as current_bucket,
                    SUM(CASE WHEN {$diff} >  {$b1} AND {$diff} <= {$b2} THEN p.due ELSE 0 END) as bucket1,
                    SUM(CASE WHEN {$diff} >  {$b2} AND {$diff} <= {$b3} THEN p.due ELSE 0 END) as bucket2,
                    SUM(CASE WHEN {$diff} >  {$b3} THEN p.due ELSE 0 END) as bucket3,
                    SUM(p.due) as total
                ")
                ->where('p.company_id', $companyId)
                ->where('p.due', '>', 0)
                ->groupBy('p.supplier_id', 's2.name')
                ->orderBy('s2.name')
                ->get();
        }

        $this->rows = collect($rows)->map(function ($r) {
            return [
                'name'    => $r->name,
                'current' => (float) $r->current_bucket,
                'b1'      => (float) $r->bucket1,
                'b2'      => (float) $r->bucket2,
                'b3'      => (float) $r->bucket3,
                'total'   => (float) $r->total,
            ];
        })->values()->all();
    }

    /** @param array<int,array<string,mixed>> $rows */
    protected function makeCsv(array $rows, array $labels): string
    {
        $out = fopen('php://temp', 'r+');
        fputcsv($out, ['Name', $labels['current'], $labels['b1'], $labels['b2'], $labels['b3'], 'Total']);
        foreach ($rows as $r) {
            fputcsv($out, [
                $r['name'],
                number_format($r['current'], 2),
                number_format($r['b1'], 2),
                number_format($r['b2'], 2),
                number_format($r['b3'], 2),
                number_format($r['total'], 2),
            ]);
        }
        rewind($out);
        return stream_get_contents($out) ?: '';
    }
}
