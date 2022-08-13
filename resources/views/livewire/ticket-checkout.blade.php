<div
	class="grid grid-cols-2 gap-2"
	x-init="stripe = Stripe(@js(config('services.stripe.key')))"
	x-data="{
		stripe: null,

		async openCheckoutDialog() {
			const clientSecret = await $wire.createPaymentIntent()
			console.log(clientSecret)
			elements = this.stripe.elements({ clientSecret })

			const paymentElement = elements.create('payment');
			paymentElement.mount('#payment-element');

			$refs.checkoutDialog.showModal()
		}
	}"
>
	<div>
		<label for="price" class="block text-sm font-bold text-gray-700">Price</label>
		<div class="relative mt-1 rounded-md shadow-sm">
			<div class="flex absolute inset-y-0 left-0 items-center pl-3 pointer-events-none">
				<span class="text-gray-500 sm:text-sm">$</span>
			</div>
			<input type="text" value="{{ $this->price_in_dollars }}" id="price" class="block pr-12 pl-7 w-full rounded-md border-none sm:text-sm focus:ring-sky-500 focus:border-sky-500" aria-describedby="price-currency" disabled>
			<div class="flex absolute inset-y-0 right-0 items-center pr-3 pointer-events-none">
				<span class="text-gray-500 sm:text-sm" id="price-currency">USD</span>
			</div>
		</div>
	</div>
	<div>
		<label for="quantity" class="block text-sm font-bold text-gray-700">
			<abbr title="Quantity" class="no-underline">Qty</abbr>
		</label>
		<div class="relative mt-1 rounded-md shadow-sm">
			<input
				id="quantity"
				type="number"
				min="1"
				wire:model="quantity"
				@class([
					'block pr-10 w-full rounded-md sm:text-sm focus:outline-none',
					'text-red-900 border-red-300 focus:border-red-500 focus:ring-red-500' => $errors->has('quantity'),
					'text-gray-900 border-gray-300 focus:border-gray-500 focus:ring-gray-500' => ! $errors->has('quantity'),
				])
				@error('quantity') aria-invalid="true" aria-describedby="quantity-error" @enderror>
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
		<button
			x-ref="buyTickets"
			x-on:click="openCheckoutDialog()"
			type="button"
			class="py-2 px-4 w-full text-base font-bold text-white rounded-md border border-transparent shadow-sm focus:ring-2 focus:ring-offset-2 focus:outline-none bg-sky-600 hover:bg-sky-700 focus:ring-sky-500"
		>Buy Tickets</button>
	</div>

	<dialog x-ref="checkoutDialog">
		<div class="max-w-md w-full space-y-8">
			<div>
				<img class="mx-auto h-12 w-auto" src="{{ asset('small-logo-pest.png') }}" alt="Logo">
				<h2 class="mt-6 text-center text-3xl font-extrabold text-gray-700">Pay</h2>
			</div>
			<form id="form-i" class="mt-8 space-y-6" action="/test" method="post">
				@csrf
				<div class="rounded-md shadow-sm space-y-4">
					<div>
						<label for="email" class="sr-only">Email</label>
						<div class="mt-1 relative rounded-md shadow-sm">
							<div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
								<x-heroicon-s-mail class="h-5 w-5 text-gray-400"/>
							</div>
							<input type="email" name="email" id="email" class="focus:ring-sky-500 focus:border-sky-500 block w-full pl-10 sm:text-sm border-gray-300 rounded-md" placeholder="you@example.com">
						</div>
					</div>
					<div id="payment-element">
						<!--Stripe.js injects the Payment Element-->
					</div>
				</div>

				<div>
					<button type="submit" class="group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-sky-600 hover:bg-sky-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-sky-500">
						<span class="absolute left-0 inset-y-0 flex items-center pl-3">
							<x-heroicon-s-lock-closed class="h-5 w-5 text-sky-500 group-hover:text-sky-400" />
						</span>
						Pay {{ $this->total_price_in_dollars }}
					</button>
				</div>
			</form>
		</div>
	</dialog>
</div>


@push('head-scripts')
<script src="https://js.stripe.com/v3/"></script>
@endpush