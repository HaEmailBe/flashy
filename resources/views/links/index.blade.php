@extends('layouts.main')

@section('title', 'Links App | All Links')

@section('content')
    <main class="py-5">
        <div class="container" style="max-width: 80%;">
            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header card-title">
                            <div class="d-flex align-items-center">
                                <h2 class="mb-0">All Links</h2>
                                <div class="ms-auto">
                                    <a href="{{ route('web.links.create') }}" class="btn btn-success"><i
                                            class="fa fa-plus-circle"></i> Add New</a>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            @include('links._filter')
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th scope="col">#</th>
                                        <th scope="col">ID</th>
                                        <th scope="col">Slug</th>
                                        <th scope="col">Total Hits</th>
                                        <th scope="col">Target_URL</th>
                                        <th scope="col">Active</th>
                                        <th scope="col">Created at</th>
                                        <th scope="col">Updated at</th>
                                        <th scope="col">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($links as $index => $link)
                                        @include('links._link', [
                                            'link' => $link,
                                            'index' => $index,
                                        ])
                                    @empty
                                        @include('links._empty')
                                    @endforelse
                                    {{-- @each('contacts._contact', $contacts, 'contact', 'contacts._empty') --}}
                                </tbody>
                            </table>
                            {{ $links->withQueryString()->links('pagination::bootstrap-5') }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
@endsection
