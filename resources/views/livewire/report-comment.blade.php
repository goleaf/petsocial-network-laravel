<div>
    @if ($reported)
        <p>Reported</p>
    @else
        <form wire:submit.prevent="report">
            <textarea wire:model="reason" placeholder="Why are you reporting this?"></textarea>
            <button type="submit">Report</button>
        </form>
    @endif
</div>
