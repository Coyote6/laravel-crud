<div>
    <form wire:submit.prevent="import">
        <x-notifications.dialog-modal wire:model="showModal">
            <x-slot name="title">Import</x-slot>

            <x-slot name="content">
                @unless ($upload)
	                <div class="py-12 flex flex-col items-center justify-center ">
	                    <div class="flex items-center space-x-2 text-xl">
	                        <x-icons.upload class="text-cool-gray-400 h-8 w-8" />
	                        <x-inputs.file-upload wire:model="upload" id="upload"><span class="text-cool-gray-500 font-bold">CSV File</span></x-input.file-upload>
	                    </div>
	                    @error('upload') 
		                    <div class="mt-3 text-red-500 text-sm">{{ $message }}</div> 
		                @enderror
	                </div>
                @else
	                <div>
						@foreach ($fieldColumnMap as $columnMap => $suggestedColumn)
		                    <x-inputs.group for="{{ $columnMap }}" label="{{ $columnMap }}" :error="$errors->first($columnMap)">
		                        <x-inputs.select wire:model="fieldColumnMap.{{ $columnMap }}" id="{{ $columnMap }}">
		                            <option value="" disabled>Select Column...</option>
		                            @foreach ($columns as $column)
		                                <option>{{ $column }}</option>
		                            @endforeach
		                        </x-inputs.select>
		                    </x-input.group>
	                    @endforeach
	                </div>
                @endif
            </x-slot>

            <x-slot name="footer">
                <x-button.secondary wire:click="$set('showModal', false)">Cancel</x-button.secondary>

                <x-button.primary type="submit">Import</x-button.primary>
            </x-slot>
        </x-notifications.dialog-modal>
    </form>
</div>