@props(['component'])

<div class="build-catalog" 
     data-id="{{ $component->id }}"
     data-type="{{ $component->component_type }}"
     data-name="{{ $component->label }}"
     data-price="{{ $component->price }}"
     data-image="{{ asset('storage/' . str_replace('\\', '/', $component->image)) }}"
     data-model="{{ isset($component->model_3d) ? asset('storage/' . $component->model_3d) : '' }}">
    <img src="{{ asset('storage/' . str_replace('\\', '/', $component->image)) }}" alt="{{ $component->label }}">
    <div>
        <p>{{ $component->label }}</p>
        <p class="text-[10px]">{{ $component->sold_count}} sold</p>
        <p>{{ $component->price_display ?? ('₱' . number_format($component->price, 2)) }}</p>   
    </div>
    <button @click="showViewModal = true; selectedComponent = {{ json_encode($component, JSON_HEX_APOS | JSON_HEX_QUOT) }}">
        See specs
    </button>
</div>