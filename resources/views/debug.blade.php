@extends('layouts.app')

@section('content')
{{ json_encode(get_defined_vars()) }}

@endsection