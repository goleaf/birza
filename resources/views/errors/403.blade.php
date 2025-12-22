@extends('errors::minimal')

@section('title', __('ui.forbidden'))
@section('code', '403')
@section('message', $exception->getMessage() ?: __('ui.forbidden'))
