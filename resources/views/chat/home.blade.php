@extends('chat.common.app')
@section('style')
    <style>
        .m-ui-content {
            width: 100%;
            height: 100vh;
            position: relative;
        }
    </style>
@endsection
@section('content')
    <div>
        <Layout class="ivu-layout m-ui-content">
            <Header class="ivu-layout-header">Header</Header>
            <Content class="ivu-layout-content">Content</Content>
            <Footer class="ivu-layout-footer">
                <p style="text-align: center">Copyright © 2018-{{ date('Y') }} Mister Pan.</p>
            </Footer>
        </Layout>
    </div>
@endsection