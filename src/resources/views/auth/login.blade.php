@extends('layout.app')

@section('content')
    <div class="bg-white py-6 sm:py-8 lg:py-12">
        <div class="mx-auto max-w-screen-2xl px-4 md:px-8">
            <h2 class="mb-4 text-center text-2xl font-bold text-gray-800 md:mb-8 lg:text-3xl">ログイン</h2>

            <form action="{{ route('login.handle') }}" class="mx-auto max-w-lg rounded-lg border" method="POST">
                @csrf
                <div class="flex flex-col gap-4 p-4 md:p-8">
                    @error('message')
                    <span class="text-red-400">{{ $message }}</span>
                    @enderror
                    <div>
                        <label class="mb-2 inline-block text-sm text-gray-800 sm:text-base" for="email">メールアドレス</label>
                        <input class="w-full rounded border bg-gray-50 px-3 py-2 text-gray-800 outline-none ring-indigo-300 transition duration-100 focus:ring" name="email" type="email">
                        @error('email')
                            <span class="text-red-400">{{ $message }}</span>
                        @enderror
                    </div>

                    <div>
                        <label class="mb-2 inline-block text-sm text-gray-800 sm:text-base" for="password">パスワード</label>
                        <input class="w-full rounded border bg-gray-50 px-3 py-2 text-gray-800 outline-none ring-indigo-300 transition duration-100 focus:ring" name="password" type="password">
                        @error('password')
                            <span class="text-red-400">{{ $message }}</span>
                        @enderror
                    </div>

                    <button class="block rounded-lg bg-gray-800 px-8 py-3 text-center text-sm font-semibold text-white outline-none ring-gray-300 transition duration-100 hover:bg-gray-700 focus-visible:ring active:bg-gray-600 md:text-base">
                        ログイン
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
