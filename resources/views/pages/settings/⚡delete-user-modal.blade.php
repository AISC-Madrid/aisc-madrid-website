<?php

use App\Concerns\PasswordValidationRules;
use App\Livewire\Actions\Logout;
use App\Models\Member;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

new class extends Component {
    use PasswordValidationRules;

    public string $password = '';

    /**
     * Deactivate the authenticated member and log them out.
     *
     * The members row is kept (not deleted) so speaker and alumni history survive;
     * an inactive member can no longer log in until an admin reactivates them.
     */
    public function deleteUser(Logout $logout): void
    {
        $this->validate([
            'password' => $this->currentPasswordRules(),
        ]);

        /** @var Member $member */
        $member = Auth::user();

        $member->update(['active' => false]);

        $logout();

        $this->redirect('/', navigate: true);
    }
}; ?>

<flux:modal name="confirm-user-deletion" :show="$errors->isNotEmpty()" focusable class="max-w-lg">
    <form method="POST" wire:submit="deleteUser" class="space-y-6">
        <div>
            <flux:heading size="lg">{{ __('Are you sure you want to deactivate your account?') }}</flux:heading>

            <flux:subheading>
                {{ __('You will be logged out and will not be able to log in again until an admin reactivates your account. Please enter your password to confirm.') }}
            </flux:subheading>
        </div>

        <flux:input wire:model="password" :label="__('Password')" type="password" viewable />

        <div class="flex justify-end space-x-2 rtl:space-x-reverse">
            <flux:modal.close>
                <flux:button variant="filled">{{ __('Cancel') }}</flux:button>
            </flux:modal.close>

            <flux:button variant="danger" type="submit" data-test="confirm-delete-user-button">
                {{ __('Deactivate account') }}
            </flux:button>
        </div>
    </form>
</flux:modal>
