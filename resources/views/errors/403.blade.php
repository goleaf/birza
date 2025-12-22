@extends('errors::minimal')

@section('title', __('common.forbidden'))
@section('code', '403')
@section('message', $exception->getMessage() ?: __('common.forbidden'))
