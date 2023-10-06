@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">{{ __('Facebook Reels Posting') }}</div>

                <div class="card-body">
                    <form action="{{ route('post.reel') }}" method="post" enctype="multipart/form-data">
                        @csrf
                        <div class="form-group">
                            <label for="title">Title</label>
                            <input type="text" class="form-control" id="title" name="title" required>
                        </div>
                        <div class="form-group">
                            <label for="description">Description</label>
                            <textarea class="form-control" id="description" name="description" rows="3" required></textarea>
                        </div>
                        <div class="form-group">
                            <label for="video">Video</label>
                            <input type="file" class="form-control" id="video" name="video" accept="video/*" required>
                        </div>
                        <br>
                        <button type="submit" class="btn btn-primary">Post Video</button>
                    </form>

                    {{--                    @if (session('status'))--}}
{{--                        <div class="alert alert-success" role="alert">--}}
{{--                            {{ session('status') }}--}}
{{--                        </div>--}}
{{--                    @endif--}}

{{--                    {{ __('You are logged in!') }}--}}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
