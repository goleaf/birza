@extends('errors::minimal')

@section('title', __('common.too_many_requests'))
@section('code', '429')
@section('message', __('common.too_many_requests'))
