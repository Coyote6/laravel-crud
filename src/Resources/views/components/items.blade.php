<div class="flex-col space-y-4">
	<x-layouts.table>
		<x-slot name="head">
			<x-layouts.table.heading class="pr-0">
				<x-inputs.checkbox wire:model="selectPage" />
			</x-layouts.table.heading>
			<x-layouts.table.heading class="w-full" sortable multi-column wire:click="sortBy('name')" :direction="$sorts['name'] ?? null">Name</x-layout.table.heading>
			<x-layouts.table.heading />
		</x-slot>
		<x-slot name="body">
		
			@if ($selectPage)
				<x-layouts.table.row class="bg-cool-gray-200" wire:key="row-message">
					<x-layouts.table.cell colspan="3">
						@unless ($selectAll)
							<div>
								<p>You have selected <strong>{{ count ($selected) }}</strong> items. Do you want to select all <strong>{{ $items->total() }}</strong> items?</p>
								<x-buttons.primary wire:click="$toggle('selectAll')" class="my-2">Yes, Please Select All</x-buttons.primary>
							</div>
						@else
							<p>You are currently selecting all <strong>{{ $items->total() }}</strong> items.</p>
						@endunless
					</x-layouts.table.cell>
				</x-layouts.table.row>
			@endif
									
			@forelse ($items as $item)
				<x-layouts.table.row wire:loading.class.delay="opacity-50" wire:key="row-{{ $item->id }}">
					<x-layouts.table.cell class="pr-0">
						<x-inputs.checkbox wire:model="selected" value="{{ $item->id }}" />
					</x-layouts.table.cell>
					<x-layouts.table.cell>
						<span class="inline-flex space-x-2 truncate text-sm leading-5">
							<x-icons.cash class="text-cool-gray-400" />
							<p class="text-cool-gray-600 truncate">{{ $item->name }}</p>
						</span>
					</x-layouts.table.cell>
					<x-layouts.table.cell>
						<x-buttons.link wire:click="edit('{{ $item->id }}')">Edit</x-buttons.link>
					</x-layouts.table.cell>
				</x-layouts.table.row>
			@empty
				<x-layouts.table.row wire:loading.class.delay="opacity-50">
					<x-layouts.table.cell colspan="3">
						<div class="flex justify-center items-center space-x-2">
							<x-icons.cash class="h-8 w-8 text-cool-gray-400" />
							<span class="font-medium py-4 text-cool-gray-400 text-xl">No results were found.</p>
						</div>
					</x-layouts.table.cell>
				</x-layouts.table.row>
			@endforelse
		</x-slot>
	</x-layouts.table>
	<div>
		@if (count ($selected) > 0)
			<p class="text-sm text-cool-gray-400">{{ count ($selected) }} of {{ $items->total() }} selected</p>
		@endif
	</div>
	<div>
		{{ $items->links() }} 
	</div>
</div>