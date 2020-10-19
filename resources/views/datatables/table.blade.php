@extends('layouts.app'))
@section('content')

    @include('datatables::datatables._table', ['maindatatable' => 'maindatatable'])

@endsection