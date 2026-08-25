@props(['order'])

@php
    $steps = ['pending' => 'Order Placed', 'confirmed' => 'Confirmed', 'processing' => 'Processing', 'shipped' => 'Shipped', 'delivered' => 'Delivered'];
    $stepKeys = array_keys($steps);
    $currentIndex = array_search($order->status, $stepKeys, true);
    $isHalted = in_array($order->status, ['cancelled', 'refunded'], true);
@endphp

<div wire:poll.30s>
    @if ($isHalted)
        <flux:callout :variant="$order->status === 'cancelled' ? 'danger' : 'warning'"
            :icon="$order->status === 'cancelled' ? 'x-circle' : 'arrow-uturn-left'"
            :heading="$order->status === 'cancelled' ? 'Order cancelled' : 'Order refunded'"
            text="This order is no longer being shipped." />
    @else
        <ol class="flex flex-col gap-6 sm:flex-row sm:items-start sm:gap-2">
            @foreach ($steps as $key => $label)
                @php
                    $reached = $currentIndex !== false && $loop->index <= $currentIndex;
                    $isCurrent = $key === $order->status;
                @endphp
                <li class="flex flex-1 items-center gap-3 sm:flex-col sm:items-stretch sm:gap-2">
                    <div class="flex items-center gap-3 sm:flex-col sm:gap-2">
                        <span
                            @class([
                                'flex size-8 shrink-0 items-center justify-center rounded-full border text-xs font-medium',
                                'border-blue-600 bg-blue-600 text-white' => $isCurrent,
                                'border-blue-600 bg-blue-50 text-blue-600 dark:bg-blue-950' => $reached && ! $isCurrent,
                                'border-zinc-300 text-zinc-400 dark:border-zinc-600 dark:text-zinc-500' => ! $reached,
                            ])
                        >
                            @if ($reached && ! $isCurrent)
                                <flux:icon name="check" variant="micro" />
                            @else
                                {{ $loop->iteration }}
                            @endif
                        </span>
                        @unless ($loop->last)
                            <span
                                @class([
                                    'h-8 w-px sm:h-px sm:w-full sm:flex-1',
                                    'bg-blue-600' => $reached && ! $loop->last,
                                    'bg-zinc-200 dark:bg-zinc-700' => ! $reached || $loop->last,
                                ])
                            ></span>
                        @endunless
                    </div>
                    <span @class(['text-sm', 'font-medium text-blue-600 dark:text-blue-400' => $isCurrent, 'text-zinc-700 dark:text-zinc-300' => $reached && ! $isCurrent, 'text-zinc-400 dark:text-zinc-500' => ! $reached])>
                        {{ $label }}
                    </span>
                </li>
            @endforeach
        </ol>
    @endif

    <div class="mt-6 flex flex-wrap items-center gap-3 text-sm text-zinc-500 dark:text-zinc-400">
        @if ($order->shipment_id)
            <span>Shipment ID: <span class="font-mono text-zinc-700 dark:text-zinc-300">{{ $order->shipment_id }}</span></span>
        @endif

        @if ($order->tracking_url)
            <flux:button href="{{ $order->tracking_url }}" target="_blank" size="sm" icon="truck" icon:trailing="arrow-top-right-on-square">
                Track with courier
            </flux:button>
        @endif
    </div>
</div>
