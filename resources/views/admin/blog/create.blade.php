@extends('layouts.admin')

@section('title', 'New blog post')

@section('content')
    <div class="mb-6">
        <h1 class="admin-heading text-2xl font-bold">New blog post</h1>
    </div>

    <form method="POST" action="{{ route('admin.blog.store') }}" enctype="multipart/form-data">
        @csrf
        @include('admin.blog._form')
        <div class="mt-6 flex gap-3">
            <button type="submit" class="btn-primary rounded-lg px-5 py-2.5 text-sm font-semibold text-white">Create post</button>
            <a href="{{ route('admin.blog.index') }}" class="admin-btn-secondary rounded-lg px-5 py-2.5 text-sm font-medium">Cancel</a>
        </div>
    </form>
@endsection
