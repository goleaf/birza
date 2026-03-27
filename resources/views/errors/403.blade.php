@extends('errors::minimal')

@section('title', __('common_forbidden'))
@section('code', '403')
@section('message', $exception->getMessage() ?: __('common_forbidden'))
