@if($paginator->hasPages())
<div class="mt-6 flex items-center justify-between text-sm">
    @if($paginator->onFirstPage())<span class="text-slate-400">← Previous</span>@else<a class="link" href="{{ $paginator->previousPageUrl() }}">← Previous</a>@endif
    <span class="text-slate-500">Page {{ $paginator->currentPage() }} of {{ $paginator->lastPage() }}</span>
    @if($paginator->hasMorePages())<a class="link" href="{{ $paginator->nextPageUrl() }}">Next →</a>@else<span class="text-slate-400">Next →</span>@endif
</div>
@endif
