<x-app-layout>
    <x-slot name="header">
        <h2 class="text-lg sm:text-xl font-semibold text-gray-800 text-center sm:text-left">
            {{ $name }} さんのメモ履歴
        </h2>
    </x-slot>

    <div class="w-full flex justify-center">
        <div class="overflow-x-auto w-full sm:w-auto px-1">
            <table class="table-auto text-sm whitespace-nowrap mx-auto min-w-[600px] bg-white rounded">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="px-2 py-2 text-left">メモ内容</th>
                        <th class="px-2 py-2 text-center">画像</th>
                        <th class="px-2 py-2 text-center">作成者</th>
                        <th class="px-2 py-2 text-center">作成日</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($notes as $note)
                        <tr class="border-t">
                            <td class="px-2 py-2 max-w-[300px] overflow-hidden whitespace-nowrap text-ellipsis text-left">
                                {{ $note->content }}
                            </td>

                            <td class="px-2 py-2 text-center">
                                <div class="flex flex-wrap justify-center gap-2">

                                    {{-- Note に画像があればそれだけ表示 --}}
                                    @if (!empty($note->signed_url))
                                        <div class="w-20 h-20 rounded border bg-gray-100 overflow-hidden flex items-center justify-center">
                                            <img src="{{ $note->signed_url }}"
                                                alt="画像"
                                                class="object-cover w-full h-full">
                                        </div>

                                    {{-- Note に画像がなければ、Customer に紐づく画像をすべて表示 --}}
                                    @elseif (!empty($note->customer->images))
                                        @foreach ($note->customer->images as $image)
                                            <div class="w-20 h-20 rounded border bg-gray-100 overflow-hidden flex items-center justify-center">
                                                <img src="{{ asset('storage/' . $image->image_path) }}"
                                                    alt="画像"
                                                    class="object-cover w-full h-full">
                                            </div>
                                        @endforeach

                                    {{-- どちらにも画像がない場合 --}}
                                    @else
                                        <span class="text-gray-400 text-xs">画像なし</span>
                                    @endif

                                </div>
                            </td>

                            <td class="px-2 py-2 text-center whitespace-nowrap">{{ $note->created_by }}</td>
                            <td class="px-2 py-2 text-center whitespace-nowrap">{{ \Carbon\Carbon::parse($note->created_at)->format('n/j G:i') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center text-gray-500 py-4">メモはまだありません。</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="max-w-xl mx-auto px-2 sm:px-0">
        <form method="POST" action="{{ route('admin.notes.store') }}" class="mt-6 space-y-4 text-sm" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="customer_type_and_id" value="{{ $type . '_' . $id }}">

            <div>
                <label for="content" class="block font-medium text-gray-700">メモ内容</label>
                <textarea name="content" id="content" rows="3" class="w-full border rounded" required></textarea>
            </div>
            <div>
                <label for="image" class="block font-medium text-gray-700">画像（任意）</label>
                <input type="file" name="image" accept="image/*" class="w-full border rounded px-3 py-2">
            </div>

            <div class="text-center">
                <button type="submit"
                        class="w-full sm:w-auto max-w-xs mx-auto block bg-blue-600 text-white font-medium px-4 py-2 rounded hover:bg-blue-700">
                    メモを追加
                </button>
            </div>
        </form>
    </div>
</x-app-layout>
