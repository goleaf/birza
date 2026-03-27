@extends('errors::minimal')

@section('title', __('common_too_many_requests'))
@section('code', '429')
@section('message', __('common_too_many_requests'))
