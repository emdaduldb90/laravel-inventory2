@props([
  'label' => 'Select',
  'name' => 'field_id',     {{-- hidden input name (id যাবে) --}}
  'options' => [],          {{-- [id => name, ...] --}}
  'placeholder' => 'Type to search...',
  'required' => false,
])

@php
  // options কে [{id:1, name:"X"}] ফরম্যাটে পাঠাই Alpine-এ
  $items = collect($options)->map(fn($n,$i)=>['id'=>$i,'name'=>$n])->values();
@endphp

<div x-data="searchSelect({{ json_encode($items) }})" class="w-full">
    <label class="block text-sm font-medium mb-1">{{ $label }} @if($required)<span class="text-red-500">*</span>@endif</label>

    <input type="hidden" name="{{ $name }}" x-model="selectedId">

    <div class="relative">
        <input type="text"
               x-model="query"
               x-on:focus="open=true"
               x-on:click="open=true"
               x-on:keydown.escape="open=false"
               placeholder="{{ $placeholder }}"
               class="w-full rounded-md border border-gray-300 bg-white px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500"
        />
        <div x-show="open" x-transition
             class="absolute z-20 mt-1 max-h-56 w-full overflow-auto rounded-md border bg-white shadow">
            <template x-for="item in filtered()">
                <div class="cursor-pointer px-3 py-2 hover:bg-indigo-50"
                     x-on:click="choose(item)">
                    <span x-text="item.name"></span>
                </div>
            </template>
            <div x-show="filtered().length === 0" class="px-3 py-2 text-gray-500">No results</div>
        </div>
    </div>
</div>

<script>
function searchSelect(items){
  return {
    items,
    open:false,
    query:'',
    selectedId:null,
    filtered(){
      const q = this.query.toLowerCase();
      return this.items.filter(i => i.name.toLowerCase().includes(q)).slice(0,100);
    },
    choose(item){
      this.query = item.name;
      this.selectedId = item.id;
      this.open = false;
    }
  }
}
</script>
