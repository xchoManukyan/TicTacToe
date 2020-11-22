<x-app-layout>
    <x-slot name="header">
        <profile-header title="{{$user->name}}" csrf="{{ csrf_token() }}"></profile-header>
    </x-slot>
    <game :user="{{ $user }}"></game>
</x-app-layout>
