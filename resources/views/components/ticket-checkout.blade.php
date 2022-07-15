<div class="grid grid-cols-2 gap-2" x-data="checkout">
	<div>
		<label for="price" class="block text-sm font-bold text-gray-700">Price</label>
		<div class="relative mt-1 rounded-md shadow-sm">
			<div class="flex absolute inset-y-0 left-0 items-center pl-3 pointer-events-none">
				<span class="text-gray-500 sm:text-sm">&nbsp;$&nbsp;</span>
			</div>
			<input type="text" value="{{ $concert->ticket_price_in_dollars }}" id="price" class="block pr-12 pl-7 w-full rounded-md border-none sm:text-sm focus:ring-sky-500 focus:border-sky-500" aria-describedby="price-currency" disabled>
			<div class="flex absolute inset-y-0 right-0 items-center pr-3 pointer-events-none">
				<span class="text-gray-500 sm:text-sm" id="price-currency">&nbsp;USD&nbsp;</span>
			</div>
		</div>
	</div>
	<div>
		<label for="quantity" class="block text-sm font-bold text-gray-700">
			<abbr title="Quantity" class="no-underline">Qty</abbr>
		</label>
		<div class="relative mt-1 rounded-md shadow-sm">
			@error('quantity')
				@php($colors = 'text-red-900 border-red-300 focus:border-red-500 focus:ring-red-500')
			@else
				@php($colors = 'text-gray-900 border-gray-300 focus:border-gray-500 focus:ring-gray-500')
			@enderror
			<input type="number" x-model="quantity" id="quantity" class="block pr-10 w-full {{ $colors }} rounded-md sm:text-sm focus:outline-none" min="1" @error('quantity') aria-invalid="true" aria-describedby="quantity-error" @enderror>
			@error('quantity')
				<div class="flex absolute inset-y-0 right-0 items-center pr-3 pointer-events-none">
					<x-heroicon-s-exclamation-circle class="w-5 h-5 text-red-500" />
				</div>
			@enderror
		</div>
		@error('quantity')
			<p class="mt-2 text-sm text-red-600" id="quantity-error">{{ $message }}</p>
		@enderror
	</div>
	<div class="col-span-2">
		<button type="button" x-on:click="openStripe" class="py-2 px-4 w-full text-base font-bold text-white rounded-md border border-transparent shadow-sm focus:ring-2 focus:ring-offset-2 focus:outline-none bg-sky-600 hover:bg-sky-700 focus:ring-sky-500">Buy Tickets</button>
	</div>
</div>

@push('head-scripts')
<script src="https://checkout.stripe.com/checkout.js"></script>
@endpush

@push('body-scripts')
<script>
document.addEventListener('alpine:init', () => {
	Alpine.data('checkout', () => ({
		title: "{{ $concert->title }}",
		price: {{ $concert->ticket_price }},
		quantity: 1,

		stripeHandler: null,

		get description() { return this.quantity > 1 ? `${this.quantity} tickets to ${this.title}.` : `One ticket to ${this.title}.` },
		get totalPrice() { return this.quantity * this.price },
		get priceInDollars() { return (this.price / 100).toFixed(2) },
		get totalPriceInDollars() { return (this.totalPrice / 100).toFixed(2) },

		init() {
			this.stripeHandler = this.initStripe()
		},

		initStripe() {
			const handler = StripeCheckout.configure({ key: "{{ config('services.stripe.key')}}" })

			window.addEventListener('popstate', () => handler.close())

			return handler
		},

		openStripe() {
			this.stripeHandler.open({
				name: "TicketBeast",
				description: this.description,
				currency: "usd",
				allowRememberMe: false,
				panelLabel: "Pay @{{amount}}",
				amount: this.totalPrice,
				image: "{{ asset('img/checkout-icon.png') }}",
				token: token =>  this.purchaseTickets(token)
			})
		},

		async purchaseTickets(token) {
			const response = await fetch("{{ route('concerts.orders.store', ['id' => $concert->id]) }}", {
				method: 'POST',
				headers: {
					"X-CSRF-TOKEN": "{{ csrf_token() }}",
					"Content-Type": "application/json",
				},
				body: JSON.stringify({
					email: token.email,
					ticket_quantity: this.quantity,
					payment_token: token.id,
				}),
			})
			
			if (response.status === 201) {
				console.log('Charge succeeded.')
			} else {
				console.warn(`Charge failed with status ${response.status}.`)
			}
		},
	}))
})
</script>
@endpush