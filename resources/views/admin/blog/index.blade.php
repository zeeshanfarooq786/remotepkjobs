@extends('layouts.admin')

@section('title', 'Blog posts')

@section('content')
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="admin-heading text-2xl font-bold">Blog posts</h1>
            <p class="admin-hint mt-1 text-sm">Add, edit, or delete posts manually.</p>
        </div>
        <a href="{{ route('admin.blog.create') }}" class="btn-primary rounded-lg px-4 py-2.5 text-sm font-semibold text-white">New post</a>
    </div>

    <div class="admin-table-wrap">
        <table class="admin-table min-w-full text-sm">
            <thead class="text-left">
                <tr>
                    <th class="px-4 py-3 font-semibold">Title</th>
                    <th class="px-4 py-3 font-semibold">Status</th>
                    <th class="px-4 py-3 font-semibold">Published</th>
                    <th class="px-4 py-3 text-right font-semibold">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($posts as $post)
                    <tr>
                        <td class="px-4 py-3">
                            <div class="admin-heading font-medium">{{ $post->title }}</div>
                            <div class="admin-hint text-xs">{{ $post->slug }}</div>
                        </td>
                        <td class="px-4 py-3">
                            @if ($post->published_at && $post->published_at->isPast())
                                <span class="admin-badge-published rounded-full px-2 py-0.5 text-xs font-medium">Published</span>
                            @elseif ($post->published_at)
                                <span class="admin-badge-scheduled rounded-full px-2 py-0.5 text-xs font-medium">Scheduled</span>
                            @else
                                <span class="admin-badge-draft rounded-full px-2 py-0.5 text-xs font-medium">Draft</span>
                            @endif
                        </td>
                        <td class="admin-hint px-4 py-3">
                            {{ $post->published_at?->format('M j, Y g:i A') ?? '—' }}
                        </td>
                        <td class="px-4 py-3 text-right">
                            <div class="flex justify-end gap-3">
                                <a href="{{ route('admin.blog.edit', $post) }}" class="link-brand font-medium hover:underline">Edit</a>
                                <form method="POST" action="{{ route('admin.blog.destroy', $post) }}" onsubmit="return confirm('Delete this post permanently?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="font-medium hover:underline" style="color: var(--admin-danger);">Delete</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="admin-hint px-4 py-8 text-center">No blog posts yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-6">
        {{ $posts->links() }}
    </div>
@endsection
