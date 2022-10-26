<x-layouts.content>
	<h1 class="text-2x font-semibold text-gray-900">{!! $title !!}</h1>
	<div class="py-4 space-y-4">
		
		{!! $searchForm !!}
			
		<div>
			@php 
				//print_r ($selected) 
			@endphp
		</div>
		<div 
			class="flex-col space-y-4"
			x-data="{
				override : false,
				overrideClick: function (event) {
					event.stopImmediatePropagation();
					event.stopPropagation();
					event.preventDefault();
					if (this.override) {
						window.open (event.target.href);
						return false;
					}
					var component = event.target.dataset.component;
					var method = event.target.dataset.method;
					var id = event.target.dataset.id;
					window.livewire.emitTo (component, method, id);
					return false;
				}
			}"
			x-on:keydown.control.window="override = true"
			x-on:keyup.control.window="override = false"
			x-on:keydown.meta.window="override = true"
			x-on:keyup.meta.window="override = false"
		>
			<x-layouts.table>
				<x-slot name="head">
					<x-layouts.table.heading class="pr-0">
						@if ($hasBulkActions)
							<x-inputs.checkbox wire:model="selectPage" />
						@endif
					</x-layouts.table.heading>
					@foreach ($columns as $key => $columnInfo)
						@php
							$sort = $this->getColumnSort ($columnInfo, $key);
							$label = $this->getColumnLabel ($columnInfo);
							$sortable = ($sort != '');
						@endphp
						
						@if ($sortable)
							@if ($loop->last)
								<x-layouts.table.heading class="w-full" sortable multi-column wire:click="sortBy('{{ $sort }}')" :direction="$sorts[$sort] ?? null">{{ $label }}</x-layout.table.heading>
							@else
								<x-layouts.table.heading sortable multi-column wire:click="sortBy('{{ $sort }}')" :direction="$sorts[$sort] ?? null">{{ $label }}</x-layout.table.heading>
							@endif
						@else
							@if ($loop->last)
								<x-layouts.table.heading class="w-full" multi-column>{{ $label }}</x-layout.table.heading>
							@else
								<x-layouts.table.heading multi-column>{{ $label }}</x-layout.table.heading>
							@endif

						@endif
						
					@endforeach
					<x-layouts.table.heading />
				</x-slot>
				<x-slot name="body">
				
					@if ($selectPage)
						<x-layouts.table.row class="bg-cool-gray-200" wire:key="row-message">
							<x-layouts.table.cell colspan="3">
								@unless ($selectAll)
									<div>
										<p>You have selected <strong>{{ count ($selected) }}</strong> items. Do you want to select all <strong>{{ $items->total() }}</strong> items?</p>
										<x-forms-button.primary wire:click="$toggle('selectAll')" class="my-2">Yes, Please Select All</x-forms-button.primary>
									</div>
								@else
									<p>You are currently selecting all <strong>{{ $items->total() }}</strong> items.</p>
								@endunless
							</x-layouts.table.cell>
						</x-layouts.table.row>
					@endif
											
					@forelse ($items as $item)
						<x-layouts.table.row wire:delay.10ms wire:loading.class.delay="opacity-50" wire:key="row-{{ $item->id }}">
							
							@if ($hasBulkActions)
								<x-layouts.table.cell class="pr-0">
									<x-inputs.checkbox wire:model="selected" value="{{ $item->id }}" />
								</x-layouts.table.cell>
							@endif
							
							@foreach ($columns as $key => $label)
								<x-layouts.table.cell>
									<span class="inline-flex space-x-2 truncate text-sm leading-5">
										@if ($loop->first && $icon)
											<x-dynamic-component :component="$icon" class="text-cool-gray-400" />
										@endif
										<p class="text-cool-gray-600 truncate">{!! $item->$key !!}</p>
									</span>
								</x-layouts.table.cell>
							@endforeach
							
							<x-layouts.table.cell>
								@foreach ($this->operationLinks ($item) as $label => $linkAttributes)
									<a {{ $linkAttributes }}>{{ $label }}</a>
									@if (!$loop->last)
										|
									@endif
								@endforeach
							</x-layouts.table.cell>
							
						</x-layouts.table.row>
					@empty
						<x-layouts.table.row wire:loading.class.delay="opacity-50">
							<x-layouts.table.cell colspan="{{ (count ($columns) + 1) }}">
								<div class="flex justify-center items-center space-x-2">
									@if ($icon)
										<x-dynamic-component :component="$icon" class="h-8 w-8 text-cool-gray-400" />
									@endif
									<span class="font-medium py-4 text-cool-gray-400 text-xl">No results were found.</p>
								</div>
							</x-layouts.table.cell>
						</x-layouts.table.row>
					@endforelse
				</x-slot>
			</x-layouts.table>
		
			<div>
				{{ $items->links() }} 
			</div>
		</div>
		
	    @if ($createForm)
			 <x-notifications.dialog-modal wire:model.defer="showCreateModal">
		        <x-slot name="title">Create</x-slot>
				<x-slot name="content">
					@livewire ($createForm, $this->routeParameters())
				</x-slot>
	        </x-notifications.dialog-modal>
        @endif
        
	    @if ($updateForm)
	        <x-notifications.dialog-modal wire:model.defer="showEditModal">
		        <x-slot name="title">Edit</x-slot>
		        <x-slot name="content">
					@livewire ($updateForm, $this->routeParameters())
		        </x-slot>
	        </x-notifications.dialog-modal>
        @endif
        
	    @if ($deleteForm)
	        <x-notifications.dialog-modal wire:model.defer="showDeleteModal">
		        <x-slot name="title">Delete</x-slot>
		        <x-slot name="content">
	            	@livewire ($deleteForm, $this->routeParameters())
		        </x-slot>
	        </x-notifications.dialog-modal>
        @endif
        
        @if ($deleteSelectedForm)
			<x-notifications.dialog-modal wire:model.defer="showDeleteSelectedModal">
		        <x-slot name="title">Delete</x-slot>
		        <x-slot name="content">
	            	{!! $deleteSelectedForm !!}
		        </x-slot>
	        </x-notifications.dialog-modal>       
        @endif
        
        @if ($importForm)
			@livewire ($importForm)
		@endif
		
		
		@foreach ($customModals as $slug => $modal)
			<x-notifications.dialog-modal wire:model.defer="{{ $slug }}">
		        <x-slot name="title">{{ $modal['title'] }}</x-slot>
		        <x-slot name="content">
	            	@livewire ($modal['component'])
		        </x-slot>
	        </x-notifications.dialog-modal>  
		@endforeach
		
	</div>
</x-layouts.content>