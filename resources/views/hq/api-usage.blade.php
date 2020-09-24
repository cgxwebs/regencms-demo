@extends('layouts.dashboard_page')

@section('header_title', 'API Usage')

@section('content')
    @parent

    <div class="max-w-3xl mx-auto">
        <div class="api-wrap">
            <p class="api-code">
                {channel}, {tag}, {story}
            </p>
            <p class="api-desc">
                Can be a channel name, tag name, story slug, or a numeric ID prefixed with a colon
                (e.g. /blog_site, /tag_name, /page_title, or /:1234)
            </p>
        </div>

        <div class="api-wrap">
            <p class="api-code">
                GET /api/stories/{channel}
            </p>
            <p class="api-desc">
                Retrieves all published stories with visible tags; unlisted or hidden parent affects descendants
            </p>
        </div>

        <div class="api-wrap">
            <p class="api-code">
                GET /api/stories/{channel}/{tag}
            </p>
            <p class="api-desc">
                Retrieves all published stories with a visible or unlisted tag and its visible descendants
            </p>
        </div>

{{--        <div class="api-wrap">--}}
{{--            <p class="api-code">--}}
{{--                GET /api/authored/{author}/{channel}--}}
{{--            </p>--}}
{{--            <p class="api-desc">--}}
{{--                Retrieves all published stories, by author, with visible tags; unlisted or hidden parent affects descendants--}}
{{--            </p>--}}
{{--        </div>--}}

{{--        <div class="api-wrap">--}}
{{--            <p class="api-code">--}}
{{--                GET /api/authored/{author}/{channel}/{tag}--}}
{{--            </p>--}}
{{--            <p class="api-desc">--}}
{{--                Retrieves all published stories, by author, with a visible or unlisted tag and its visible descendants--}}
{{--            </p>--}}
{{--        </div>--}}

        <div class="api-wrap">
            <p class="api-code">
                GET /api/story/{channel}/{story}
            </p>
            <p class="api-desc">
                Retrieves a single published or unlisted story
            </p>
        </div>

        <div class="api-wrap">
            <p class="api-code">
                GET /api/tags/{channel}
            </p>
            <p class="api-desc">
                Retrieves all visible tags
            </p>
        </div>

        <div class="api-wrap">
            <p class="api-code">
                PARAM ?sort=newest|oldest|new-update|old-update|a-z|z-a
            </p>
            <p class="api-desc">
                Sorts collection based on parameter. Data constraints only applies to /stories while alphabetical sort
                applies to both /stories and /tags. Newest is /stories default, while a-z for /tags.
            </p>
        </div>

        <div class="api-wrap">
            <p class="api-code">
                PARAM ?page={page_hash}
            </p>
            <p class="api-desc">
                Retrieves collection by page, hash is supplied as extra data to collections.
                Applies to /stories.
            </p>
        </div>

    </div>
@endsection
