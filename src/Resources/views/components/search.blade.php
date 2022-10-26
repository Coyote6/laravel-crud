<div>
	<div class="flex justify-between">
		<div class="w-1/2 flex space-x-4">
			<x-inputs.text placeholder="Search" wire:model.debounce.500ms="filters.search" />
			
			<x-buttons.link wire:click="toggleShowFilters">
				@if ($showFilters) 
					Hide
				@endif Advanced Search
			</x-buttons.link>
		</div>
		<div class="space-x-2 flex items-center">
			
            <x-inputs.group borderless paddingless for="perPage" label="Per Page">
                <x-inputs.select wire:model="perPage" id="perPage">
                    <option value="3">3</option>
                    <option value="10">10</option>
                    <option value="25">25</option>
                    <option value="50">50</option>
                </x-input.select>
            </x-inputs.group>
			
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
			<livewire:import />
			@if ($createRoute)
				<x-buttons.primary wire:click="create"><x-icons.plus /> New</x-buttons.primary>
			@endif
		</div>
	</div>
	<div class="">
		@if ($showFilters)
            <div class="bg-cool-gray-200 p-4 rounded shadow-inner flex relative">
				<p>We have no advanced search</p>
            </div>
        @endif
	</div>
</div>