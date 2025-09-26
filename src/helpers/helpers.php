<?php
/**
 * Function to generate random string.
 * The function takes an integer n as input and generates a string by concatenating n characters chosen randomly from a domain.

N.B. In our case the integer n is randomly chosen between a range of 5 and 8. I chose this "short" range to not overdo the length of the identifier
 */
function randomString($n) {

	$generated_string = "";

	$domain = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890";

	$len = strlen($domain);

	// Loop to create random string
	for ($i = 0; $i < $n; $i++) {
		// Generate a random index to pick characters
		$index = rand(0, $len - 1);

		// Concatenating the character
		// in resultant string
		$generated_string = $generated_string . $domain[$index];
	}

	return $generated_string;
}

/**
 *
 */
function getSecureRandomToken() {
	$token = bin2hex(openssl_random_pseudo_bytes(16));
	return $token;
}

/**
 * Clear Auth Cookie
 */
function clearAuthCookie() {

	unset($_COOKIE['series_id']);
	unset($_COOKIE['remember_token']);
	setcookie('series_id', '', -1, '/');
	setcookie('remember_token', '', -1, '/');
}
/**
 *
 */
function clean_input($data) {
        $data = trim($data);
        $data = stripslashes($data);
        $data = htmlspecialchars($data);
        return $data;
}

/**
 * Decode HTML special characters that may have been encoded multiple times.
 *
 * Older versions of the application stored values using htmlspecialchars() and
 * that resulted in data being persisted as "&amp;". When those values were
 * rendered again they were encoded repeatedly ("&amp;amp;" and so on).  This
 * helper normalises the stored value by decoding the HTML entities until the
 * string no longer changes, effectively giving us the original raw value.
 */
function normalize_html_entities($value, int $flags = ENT_QUOTES): string {
        $normalized = (string)($value ?? '');

        do {
                $previous = $normalized;
                $normalized = htmlspecialchars_decode($previous, $flags);
        } while ($normalized !== $previous);

        return $normalized;
}

/**
 * Escape a value for safe output in HTML contexts.
 *
 * It first normalises the stored value (see normalize_html_entities()) and then
 * applies htmlspecialchars() so that we never double-encode ampersands or
 * other entities when rendering database values inside HTML attributes or
 * plain text.
 */
function escape_output($value, int $flags = ENT_QUOTES): string {
        $normalized = normalize_html_entities($value, $flags);

        return htmlspecialchars($normalized, $flags, 'UTF-8');
}

function paginationLinks($current_page, $total_pages, $base_url) {

	if ($total_pages <= 1) {
		return false;
	}

	$html = '';

	if (!empty($_GET)) {
		// We must unset $_GET[page] if previously built by http_build_query function
		unset($_GET['page']);
		// To keep the query sting parameters intact while navigating to next/prev page,
		$http_query = "?" . http_build_query($_GET);
	} else {
		$http_query = "?";
	}

	$html = '<ul class="pagination pagination-sm m-0 float-right">';

	if ($current_page == 1) {

		$html .= '<li class="page-item disabled"><a class="page-link">First</a></li>';
	} else {
		$html .= '<li class="page-item"><a class="page-link" href="' . $base_url . $http_query . '&page=1">First</a></li>';
	}

	// Show pagination links

	//var i = (Number(data.page) > 5 ? Number(data.page) - 4 : 1);

	if ($current_page > 5) {
		$i = $current_page - 4;
	} else {
		$i = 1;
	}

	for (; $i <= ($current_page + 4) && ($i <= $total_pages); $i++) {
		($current_page == $i) ? $li_class = ' class="active"' : $li_class = '';

		$link = $base_url . $http_query;

		$html = $html . '<li class="page-item"' . $li_class . '><a class="page-link" href="' . $link . '&page=' . $i . '">' . $i . '</a></li>';

		if ($i == $current_page + 4 && $i < $total_pages) {

			$html = $html . '<li class="page-item disabled"><a class="page-link">...</a></li>';

		}

	}

	if ($current_page == $total_pages) {
		$html .= '<li class="page-item disabled"><a class="page-link">Last</a></li>';
	} else {

		$html .= '<li class="page-item"><a class="page-link" href="' . $base_url . $http_query . '&page=' . $total_pages . '">Last</a></li>';
	}

	$html = $html . '</ul>';

	return $html;
}

function base_url() {
    require_once(__DIR__ . '/../config/environment.php');
    if (defined('BASE_URL') && BASE_URL !== null) {
        return BASE_URL;
    } else {
        return sprintf(
            "%s://%s:%s",
            isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ? 'https' : 'http',
            $_SERVER['SERVER_NAME'],
            $_SERVER['SERVER_PORT']
        );
    }
}
