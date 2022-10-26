<form {{ $attributes->merge([
	'class' => ($attributes->get('disabled') ? ' opacity-75 cursor-not-allowed' : ''),
	'x-data' => '{ showFilters : false }'
]) }}>

	<div class="flex justify-between ontent-center items-center">
		<div class="w-1/2 flex space-x-4 content-center items-center">
			{!! $fields['search'] !!}
			{!! $fields['advanced-filters-button'] !!}
		</div>
		
		<div class="space-x-2 flex items-center">
			
			{!! $fields['per-page'] !!}
			
			@if ($hasBulkActions)
				<x-dropdowns.dropdown label="Bulk Actions">
					<x-dropdowns.item wire:click="exportSelected" type="button" class="flex items-center space-x-2">
						<x-icons.download class="text-cool-gray-400" /> 
						<span>Export</span>
					</x-dropdowns.item>
					<x-dropdowns.item wire:click="$toggle('showDeleteSelectedModal')" type="button" class="flex items-center space-x-2">
						<x-icons.trash class="text-cool-gray-400" />
						<span>Delete</span>
					</x-dropdowns.item>
					
				</x-dropdowns.dropdown>
			@endif
			
			@if ($buttons)
				@foreach ($buttons as $label => $attrs)
					@if ($attrs->offsetExists ('href'))
						<a {{ $attrs }} class="py-2 px-4 border rounded-md text-sm leading-5 font-medium focus:outline-none focus:border-blue-300 focus:shadow-outline-blue transition duration-150 ease-in-out border-gray-300 text-gray-700 active:bg-gray-50 active:text-gray-800 hover:text-gray-500 flex items-center space-x-2"><span>{{ $label }}</span></a>
					@else
						<x-button.secondary :attributes="$attrs" class="flex items-center space-x-2"><span>{{ $label }}</span></x-button.secondary>
					@endif
				@endforeach
			@endif

			@if ($hasImportForm)
				<x-button.secondary wire:click="toggleImportModal" class="flex items-center space-x-2"><x-icons.upload class="text-cool-gray-500"/> <span>Import</span></x-button.secondary>
			@endif
			
			@if ($hasCreateForm)
				<a {!! !$createRoute ?: 'href="' . $createRoute . '"' !!} class="border-indigo-600 py-2 px-4 border rounded-md text-sm leading-5 font-medium focus:outline-none focus:border-blue-300 focus:shadow-outline-blue transition duration-150 ease-in-out text-white bg-indigo-600 hover:bg-indigo-500 active:bg-indigo-700 border-indigo-600 border-indigo-600" {!! !$useCreateModal ?: 'wire:click.prevent="$toggle(\'showCreateModal\')"' !!}><x-icons.plus class="inline-block" /> New</a>
			@endif
			
		</div>
	</div>
	<div class="">
        <div x-show="showFilters" class="bg-cool-gray-200 mt-4 p-4 rounded shadow-inner flex relative">
			
			@error ('form')
				<x-forms-error 
					:display='$has_error' 
					:errorAttributes='$error_message_attributes' 
					:container_attributes='$error_message_container_attributes'
				>{{ $message }}</x-forms-error>
			@enderror
			
			@foreach ($fields as $field)
				{!! $field !!}
			@endforeach
			
			<div class="actions flex items-center space-x-2.5">
		
				@foreach ($hidden_fields as $field)
					{!! $field !!}
				@endforeach
				
				@csrf
				@method($method)
				
			</div>
        </div>
	</div>
	
</form>		

@if ($has_confirm_field && $is_livewire_form)
	@push('scripts')
		<script>
			Livewire.on('updatedConfirmation', name => {
				var el = document.getElementById(name);
				var	val = el.value;
				@this.set(name, val);
			});
		</script>
	@endpush
@endif
	

