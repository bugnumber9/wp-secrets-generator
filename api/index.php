<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Authentication Unique Keys and Salts Generator for WordPress</title>
<style>
	* {
		box-sizing: border-box;
		margin: 0;
		padding: 0;
	}

	html, body {
		height: 100%;
		margin: 0;
		padding: 0;
		overflow-x: hidden;
	}

	body {
		background-color: #f4f2ef;
		font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, "Helvetica Neue", sans-serif;
		display: flex;
		flex-direction: column;
		justify-content: center;
		align-items: center;
		height: 100vh;
	}

	.container {
		text-align: center;
		flex: 1;
		max-width: 90vw;
		overflow-y: auto;
		display: flex;
		flex-direction: column;
		justify-content: center;
	}

	.result-box {
		border: 1px solid #ccc;
		padding: 20px;
		margin-top: 1em;
		background-color: #f9f9f9;
		display: inline-block;
		text-align: left;
		max-width: 100%;
		overflow-y: auto;
		border-radius: 5px;
	}

	h1 { color: #0b0c0c; }

	form { margin-top: 1em; }
			
	pre {
		margin: 0;
		font-family: 'Courier New', Courier, monospace;
		font-size: 1rem;
		color: #0b0c0c;
		line-height: 1.5;
	}

	button {
		padding: 10px 20px;
		font-size: 1rem;
		cursor: pointer;
		background-color: #1d70b8;
		color: #fff;
		border: none;
		border-radius: 5px;
		outline: none;
	}

	button:focus {
		outline: 3px solid #0b0c0c;
		outline-offset: 3px;
	}

	button:hover {
		background-color: #0b0c0c;
		color: #fff;
	}

	.copy-button {
		display: block;
		margin-left: auto;
		margin-right: auto;
		margin-top: 1em;
	}

	#copy-message {
		margin-top: 1em;
		min-height: 1.5em;
	}

	footer {
		background-color: #f4f2ef;
		text-align: center;
		padding: 1em;
		width: 100%;
		border-top: 1px solid #ccc;
		color: #0b0c0c;
	}

	footer a {
		color: #1d70b8;
		text-decoration: underline;
	}

	.success {
		color: #1d70b8;
		font-size: 1rem;
	}

	.error {
		color: red;
		font-size: 1rem;
	}
</style>
<script>
	function CopyToClipboard() {
		const resultText = document.getElementById('result').innerText;
		navigator.clipboard.writeText(resultText).then(() => {
			const messageElement = document.getElementById('copy-message');
			messageElement.innerText = 'Copied to clipboard!';
			messageElement.className = 'success';
			messageElement.setAttribute('aria-live', 'polite');
		}).catch(err => {
			const messageElement = document.getElementById('copy-message');
			messageElement.innerText = 'Failed to copy to clipboard!';
			messageElement.className = 'error';
			messageElement.setAttribute('aria-live', 'assertive');
		});
	}
</script>
</head>
<body>
	<div class="container" role="main">
		<h1>Authentication Unique Keys and Salts Generator for WordPress</h1>
		<form method="post">
			<button type="submit" name="generate" aria-label="Generate a set of keys and salts">Generate</button>
		</form>
	<?php
		if ( isset( $_POST['generate'] ) ) {
			echo '<div class="result-box" id="result" role="alert" aria-live="assertive">';
			generate_all_secrets();
			echo '</div>';
			echo '<button class="copy-button" onclick="CopyToClipboard()" aria-label="Copy generated keys and salts to clipboard">Copy</button>';
			echo '<div class="message" id="copy-message" role="status" aria-live="polite"></div>';
		}
	?>
	</div>
	<footer role="contentinfo">
		<p>Version 0.1 | <a href="https://github.com/bugnumber9/wp-secrets-generator" rel="noopener noreferrer" aria-label="Visit the GitHub repository">GitHub</a></p>
	</footer>
</body>
</html>

<?php

function generate_all_secrets() {
	$auth_key = generate_secret( 64, true, true );
	$secure_auth_key = generate_secret( 64, true, true );
	$logged_in_key = generate_secret( 64, true, true );
	$nonce_key = generate_secret( 64, true, true );
	$auth_salt = generate_secret( 64, true, true );
	$secure_auth_salt = generate_secret( 64, true, true );
	$logged_in_salt = generate_secret( 64, true, true );
	$nonce_salt = generate_secret( 64, true, true );

	echo '<pre>';
	echo 'define( \'AUTH_KEY\',' . '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' . '\'' . htmlspecialchars($auth_key) . '\' );';
	echo '<br>';
	echo 'define( \'SECURE_AUTH_KEY\',' . '&nbsp;&nbsp;' . '\'' . htmlspecialchars($secure_auth_key) . '\' );';
	echo '<br>';
	echo 'define( \'LOGGED_IN_KEY\',' . '&nbsp;&nbsp;&nbsp;&nbsp;' . '\'' . htmlspecialchars($logged_in_key) . '\' );';
	echo '<br>';
	echo 'define( \'NONCE_KEY\',' . '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' . '\'' . htmlspecialchars($nonce_key) . '\' );';
	echo '<br>';
	echo 'define( \'AUTH_SALT\',' . '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' . '\'' . htmlspecialchars($auth_salt) . '\' );';
	echo '<br>';
	echo 'define( \'SECURE_AUTH_SALT\',' . '&nbsp;' . '\'' . htmlspecialchars($secure_auth_salt) . '\' );';
	echo '<br>';
	echo 'define( \'LOGGED_IN_SALT\',' . '&nbsp;&nbsp;&nbsp;' . '\'' . htmlspecialchars($logged_in_salt) . '\' );';
	echo '<br>';
	echo 'define( \'NONCE_SALT\',' . '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' . '\'' . htmlspecialchars($nonce_salt) . '\' );';
	echo '</pre>';
}

// Modified version of https://developer.wordpress.org/reference/functions/wp_generate_password/
function generate_secret( $length = 12, $special_chars = true, $extra_special_chars = false ) {
	$chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
	if ( $special_chars ) {
		$chars .= '!@#$%^&*()';
	}
	if ( $extra_special_chars ) {
		$chars .= '-_ []{}<>~`+=,.;:/?|';
	}

	$password = '';
	for ( $i = 0; $i < $length; $i++ ) {
		$password .= substr( $chars, generate_rand( 0, strlen( $chars ) - 1 ), 1 );
	}

	return $password;
}

// Modified version of https://developer.wordpress.org/reference/functions/wp_rand/
function generate_rand( $min = null, $max = null ) {
	$randomizer = rand( 1, 256 );
	$rnd_value = bin2hex( random_bytes( $randomizer ) );

	/*
	 * Some misconfigured 32-bit environments (Entropy PHP, for example)
	 * truncate integers larger than PHP_INT_MAX to PHP_INT_MAX rather than overflowing them to floats.
	 */
	$max_random_number = 3000000000 === 2147483647 ? (float) '4294967295' : 4294967295; // 4294967295 = 0xffffffff

	if ( null === $min ) {
		$min = 0;
	}

	if ( null === $max ) {
		$max = $max_random_number;
	}

	// We only handle ints, floats are truncated to their integer value.
	$min = (int) $min;
	$max = (int) $max;

	// Use PHP's CSPRNG, or a compatible method.
	static $use_random_int_functionality = true;
	if ( $use_random_int_functionality ) {
		try {
			// wp_rand() can accept arguments in either order, PHP cannot.
			$_max = max( $min, $max );
			$_min = min( $min, $max );
			$val  = random_int( $_min, $_max );
			if ( false !== $val ) {
				return absint( $val );
			} else {
				$use_random_int_functionality = false;
			}
		} catch ( Error $e ) {
			$use_random_int_functionality = false;
		} catch ( Exception $e ) {
			$use_random_int_functionality = false;
		}
	}

	/*
	 * Reset $rnd_value after 14 uses.
	 * 32 (md5) + 40 (sha1) + 40 (sha1) / 8 = 14 random numbers from $rnd_value.
	 */
	if ( strlen( $rnd_value ) < 8 ) {

		static $seed = '';

		$rnd_value  = md5( uniqid( microtime() . mt_rand(), true ) . $seed );
		$rnd_value .= sha1( $rnd_value );
		$rnd_value .= sha1( $rnd_value . $seed );
		$seed       = md5( $seed . $rnd_value );
	}

	// Take the first 8 digits for our value.
	$value = substr( $rnd_value, 0, 8 );

	// Strip the first eight, leaving the remainder for the next call to wp_rand().
	$rnd_value = substr( $rnd_value, 8 );

	$value = abs( hexdec( $value ) );

	// Reduce the value to be within the min - max range.
	$value = $min + ( $max - $min + 1 ) * $value / ( $max_random_number + 1 );

	return abs( (int) $value );
}