@extends('layouts.admin')

@section('title', 'Edit blog post')

@section('content')
    <div class="mb-6">
        <h1 class="admin-heading text-2xl font-bold">Edit blog post</h1>
    </div>

    <form method="POST" action="{{ route('admin.blog.update', $post) }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        @include('admin.blog._form', ['post' => $post])
        <div class="mt-6 flex flex-wrap gap-3">
            <button type="submit" class="btn-primary rounded-lg px-5 py-2.5 text-sm font-semibold text-white">Save changes</button>
            <a href="{{ route('admin.blog.index') }}" class="admin-btn-secondary rounded-lg px-5 py-2.5 text-sm font-medium">Back to list</a>
        </div>
    </form>

    <form method="POST" action="{{ route('admin.blog.destroy', $post) }}" class="mt-8 border-t pt-6" style="border-color: var(--admin-border);"
        onsubmit="return confirm('Delete this post permanently?');">
        @csrf
        @method('DELETE')
        <button type="submit" class="text-sm font-medium hover:underline" style="color: var(--admin-danger);">Delete this post</button>
    </form>
@endsection
