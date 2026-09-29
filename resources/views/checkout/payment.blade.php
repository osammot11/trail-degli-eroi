<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pagamento - Trail degli Eroi</title>
    <link rel="stylesheet" href="{{ asset('css/style.css?v=1.8') }}">
    <script src="https://js.stripe.com/v3/"></script>
</head>
<body class="center-items checkout-body">

    <div class="checkout-wrapper checkout-narrow stack-large">
        <div class="stack-mid center-text">
            <p class="checkout-kicker">Ordine {{ $order->group_code }}</p>
            <h1>Pagamento iscrizione</h1>
            <p>Completa il pagamento con carta senza uscire dal sito.</p>
        </div>

        <div class="checkout-summary stack-mid">
            <div class="checkout-summary-row">
                <span>Partecipanti</span>
                <strong>{{ $order->tickets->count() }}</strong>
            </div>
            <div class="checkout-summary-row">
                <span>Metodo</span>
                <strong>Carta</strong>
            </div>
            <div class="checkout-summary-row checkout-summary-total">
                <span>Totale</span>
                <strong>{{ number_format($order->total_amount, 2, ',', '.') }}€</strong>
            </div>
        </div>

        <form id="payment-form" class="stack-large">
            <div class="stripe-payment-box">
                <div id="payment-element"></div>
            </div>

            <div id="payment-message" class="payment-message hidden"></div>

            <button class="btn fullwidth" id="submit" type="submit">
                <span id="button-text">Paga {{ number_format($order->total_amount, 2, ',', '.') }}€</span>
                <span id="spinner" class="hidden">Pagamento in corso...</span>
            </button>
        </form>

        <div class="center-text">
            <a href="{{ route('iscrizione') }}" class="hyperlink">Torna al modulo iscrizione</a>
        </div>
    </div>

    <script>
        const stripe = Stripe(@json($stripeKey));
        const elements = stripe.elements({
            clientSecret: @json($clientSecret),
            appearance: {
                theme: 'stripe',
                variables: {
                    colorPrimary: '#c64735',
                    colorBackground: '#ffffff',
                    colorText: '#191d2f',
                    colorDanger: '#9e3027',
                    fontFamily: '"Barlow Condensed", sans-serif',
                    borderRadius: '6px',
                    spacingUnit: '5px',
                },
                rules: {
                    '.Input': {
                        border: '1px solid #dddddd',
                        boxShadow: 'none',
                        fontSize: '20px',
                    },
                    '.Input:focus': {
                        border: '1px solid #c64735',
                        boxShadow: '0 0 0 1px #c64735',
                    },
                    '.Label': {
                        fontSize: '20px',
                        color: '#191d2f',
                    },
                },
            },
        });

        const paymentElement = elements.create('payment', {
            layout: 'tabs',
            fields: {
                billingDetails: {
                    name: 'auto',
                    email: 'auto',
                },
            },
        });

        paymentElement.mount('#payment-element');

        const form = document.getElementById('payment-form');
        const submitButton = document.getElementById('submit');
        const buttonText = document.getElementById('button-text');
        const spinner = document.getElementById('spinner');
        const message = document.getElementById('payment-message');

        form.addEventListener('submit', async (event) => {
            event.preventDefault();
            setLoading(true);
            setMessage('');

            const { error, paymentIntent } = await stripe.confirmPayment({
                elements,
                confirmParams: {
                    return_url: @json(route('checkout.success', ['order_code' => $order->group_code])),
                    payment_method_data: {
                        billing_details: {
                            name: @json(trim(($order->tickets->first()?->first_name ?? '') . ' ' . ($order->tickets->first()?->last_name ?? ''))),
                            email: @json($order->tickets->first()?->email),
                        },
                    },
                },
                redirect: 'if_required',
            });

            if (error) {
                setMessage(error.message || 'Pagamento non riuscito. Controlla i dati della carta e riprova.');
                setLoading(false);
                return;
            }

            if (paymentIntent && paymentIntent.status === 'succeeded') {
                window.location.href = @json(route('checkout.success', ['order_code' => $order->group_code]));
                return;
            }

            setMessage('Pagamento in elaborazione. Attendi qualche secondo e controlla la conferma.');
            setLoading(false);
        });

        function setLoading(isLoading) {
            submitButton.disabled = isLoading;
            buttonText.classList.toggle('hidden', isLoading);
            spinner.classList.toggle('hidden', !isLoading);
        }

        function setMessage(text) {
            message.textContent = text;
            message.classList.toggle('hidden', !text);
        }
    </script>
</body>
</html>
