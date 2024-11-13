<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Products') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="overflow-hidden overflow-x-auto p-6 bg-white border-b border-gray-200">

                    <div class="min-w-full align-middle">

                        @if (auth()->user()->is_admin)
                            <a href="{{ route('products.create') }}" class="...">
                                Add new product
                            </a>
                        @endif

                        <h2>You will see Product 1 and other great products</h2>
                        <table class="min-w-full divide-y divide-gray-200 border">
                            <thead>
                                <tr>
                                    <th class="px-6 py-3 bg-gray-50 text-left">
                                        <span class="text-xs leading-4 font-medium text-gray-500 uppercase tracking-wider">Name</span>
                                    </th>
                                    <th class="px-6 py-3 bg-gray-50 text-left">
                                        <span class="text-xs leading-4 font-medium text-gray-500 uppercase tracking-wider">Price</span>
                                    </th>

                                    <th class="px-6 py-3 bg-gray-50 text-left">
                                        <span class="text-xs leading-4 font-medium text-gray-500 uppercase tracking-wider">Price (EUR)</span>
                                    </th>
                                </tr>
                            </thead>

                            <tbody class="bg-white divide-y divide-gray-200 divide-solid">
                                @forelse($products as $product)
                                    <tr class="bg-white">
                                        <td class="px-6 py-4 whitespace-no-wrap text-sm leading-5 text-gray-900">
                                            {{ $product->name }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-no-wrap text-sm leading-5 text-gray-900">
                                            ${{ number_format($product->price, 2) }}
                                        </td>

                                        <td class="px-6 py-4 whitespace-no-wrap text-sm leading-5 text-gray-900">
                                            {{ $product->price_eur }}
                                        </td>

                                        @if (auth()->user()->is_admin)
                                            <td class="px-6 py-4 whitespace-no-wrap text-sm leading-5 text-gray-900">
                                                <a href="{{ route('products.edit', $product) }}" class="">
                                                    Edit
                                                </a>

                                                <form action="{{ route('products.destroy', $product) }}" method="POST" class="inline-block">
                                                    @csrf
                                                    @method('DELETE')
                                                    <x-primary-button onclick="return confirm('Are you sure?')" class="bg-red-600 text-white">Delete</x-primary-button>
                                                </form>
                                            </td>
                                        @endif
                                    </tr>
                                @empty
                                    <tr class="bg-white">
                                        <td colspan="2" class="px-6 py-4 whitespace-no-wrap text-sm leading-5 text-gray-900">
                                            {{ __('No products found') }}
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
