@extends('uccello::modules.default.index.main')

@section('content')
<div class="row clearfix">
    <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
        <div class="card">
            <div class="body">
                <div class="row">
                    <div class="col-md-12">
                        <h1>PHP Outlook Sample</h1>
                        <p>This example shows how to get an OAuth token from Azure using the <a href="https://docs.microsoft.com/azure/active-directory/develop/active-directory-v2-protocols-oauth-code" target="_blank">authorization code grant flow</a> and to use that token to make calls to the Outlook APIs in the <a href="https://docs.microsoft.com/en-us/graph/overview" target="_blank">Microsoft Graph</a>.</p>
                        <p>
                            <a class="btn btn-lg btn-primary" href="{{ route('uccello.calendar.signin', ['domain' => $domain->slug]) }}" role="button" id="connect-button">Connect to Outlook</a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection