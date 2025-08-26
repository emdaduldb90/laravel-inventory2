@php
  $paper = strtolower($paper ?? 'a4');
  $orientation = strtolower($orientation ?? 'portrait');
  $margin = is_numeric($margin ?? null) ? (int)$margin : 12;

  // default content width (screen preview)
  $contentWidth = match ($paper) {
      'a5'    => '600px',
      'pos80' => '80mm',
      'pos58' => '58mm',
      default => '900px', // a4 / letter
  };
@endphp

<style>
  /* Screen preview width */
  .wrap{ max-width: {{ $contentWidth }}; }

  @media print {
    @page {
      @if(in_array($paper,['a4','letter','a5']))
        size: {{ strtoupper($paper) }} {{ $orientation }};
      @elseif($paper === 'pos80')
        size: 80mm auto;
      @elseif($paper === 'pos58')
        size: 58mm auto;
      @else
        size: A4 {{ $orientation }};
      @endif
      margin: {{ $margin }}mm;
    }
    body { margin: 0; }
    @if(in_array($paper,['pos80','pos58']))
      body, .wrap { width: {{ $contentWidth }}; max-width:none; }
      table th, table td { font-size: 11px; padding: 2px 0; }
      .logo { height: 40px; }
    @endif
  }
</style>
