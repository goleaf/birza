@extends('errors::minimal')

@section('title', __('ui.too_many_requests'))
@section('code', '429')
@section('message', __('ui.too_many_requests'))
