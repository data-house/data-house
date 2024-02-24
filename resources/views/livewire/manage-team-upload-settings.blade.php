<div>
    <x-section-border />
    
    <div class="mt-10 sm:mt-0">
        <!-- Upload settings -->
        <x-form-section submit="updateUploadSettings">
            <x-slot name="title">
                {{ __('Upload Link') }}
            </x-slot>
    
            <x-slot name="description">
                {{ __('Configure file upload by using external shares with a link.') }}
            </x-slot>
    
            <x-slot name="form">
                <div class="col-span-6">
                    <div class="max-w-xl text-sm text-stone-600">
                        {{ __('Please provide the URL of the shared folder to redirect the users to.') }}
                    </div>
                </div>
    
                <div class="col-span-6 sm:col-span-4">
                    <x-label for="uploadLink" value="{{ __('Url') }}" />
                    <x-input-error for="uploadSettingsForm.uploadLinkUrl" class="mt-2" />
                    <x-input id="uploadLink" type="text" class="mt-1 block w-full" wire:model="uploadSettingsForm.uploadLinkUrl" />
                </div>
    
                <div class="col-span-6 sm:col-span-4">
                    <x-label for="supportProjects" value="{{ __('Support upload in projects') }}" />
                    
                    <x-input-error for="uploadSettingsForm.supportProjects" class="mt-2" />

                    <label for="supportProjects" class="mt-1 flex items-center">
                        <x-checkbox id="supportProjects" value="1" wire:model="uploadSettingsForm.supportProjects" />
                        <span class="ml-2 text-sm text-stone-600">{{ __('Allow upload within projects') }}</span>
                    </label>

                </div>
    
                <div class="col-span-6 sm:col-span-4">
                    <x-label for="limitProjectsTo" value="{{ __('Limit upload to the following projects (insert project ulid)') }}" />
                    <x-input-error for="uploadSettingsForm.limitProjectsTo" class="mt-2" />
                    <x-input id="limitProjectsTo" type="text" class="mt-1 block w-full" wire:model="uploadSettingsForm.limitProjectsTo" />
                </div>
    
            </x-slot>
    
            <x-slot name="actions">
                <x-action-message class="mr-3" on="saved">
                    {{ __('Configuration saved.') }}
                </x-action-message>
    
                <x-button>
                    {{ __('Save') }}
                </x-button>
            </x-slot>
        </x-form-section>
    </div>
</div>