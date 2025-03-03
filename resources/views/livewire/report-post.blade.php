<div>
    @if ($reported)
        <p>{{ __('posts.reported_successfully') }}</p>
    @else
        <form wire:submit.prevent="report">
            <textarea wire:model="reason" placeholder="{{ __('posts.report_reason_placeholder') }}"></textarea>
            <button type="submit">{{ __('posts.submit_report') }}</button>
        </form>
    @endif
</div>
