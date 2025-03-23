<?php
/**
 * The template for displaying all pages.
 *
 * This is the template that displays all pages by default.
 * Please note that this is the WordPress construct of pages
 * and that other 'pages' on your WordPress site will use a
 * different template.
 *
 *
 * Template name: shows
 * @package storefront
 */

get_header(); ?>

<div id="primary" class="content-area">
	<main id="main" class="site-main" role="main">
		<input id="search" style="width:100%" placeholder="Search for shows..." onkeyup="updateshows()">
		<table>
			<thead>
				<tr>
					<td>
						Show
					</td>
					<td>
						Amount Raised
					</td>
					<td>
						Goal
					</td>
				</tr>
				</tr>
			</thead>
			<tbody id="showbody">
			</tbody>
		</table>

	</main><!-- #main -->
</div><!-- #primary -->
<script>
	var shows = '';
	function formatCurrency(n, c, d, t) {
		"use strict";

		var s, i, j;

		c = isNaN(c = Math.abs(c)) ? 2 : c;
		d = d === undefined ? "." : d;
		t = t === undefined ? "," : t;

		s = n < 0 ? "-" : "";
		i = parseInt(n = Math.abs(+n || 0).toFixed(c), 10) + "";
		j = (j = i.length) > 3 ? j % 3 : 0;

		return s + (j ? i.substr(0, j) + t : "") + i.substr(j).replace(/(\d{3})(?=\d)/g, "$1" + t) + (c ? d + Math.abs(n - i).toFixed(c).slice(2) : "");
	}

	function updateshows() {
		var showname = document.getElementById('search').value;
		document.getElementById('showbody').innerHTML = '';
		for (var s in shows) {
			var show = shows[s];
			if (showname == '' || show['ShowName'].toLowerCase().includes(showname.toLowerCase())) {
				var tr = document.createElement('tr');
				var td = document.createElement('td');
				td.innerHTML = '<a href="https://telethon.wruw.org/' + show['slug'] + '">' + show['ShowName'] + '</a>';
				tr.appendChild(td);
				var td = document.createElement('td');
				td.innerHTML = '$' + formatCurrency(show['total']);
				tr.appendChild(td);
				var td = document.createElement('td');
				td.innerHTML = '$' + formatCurrency(show['goal']);
				tr.appendChild(td);
				document.getElementById('showbody').appendChild(tr);
			}
		}
	}
	
	var xhttp = new XMLHttpRequest();
	xhttp.onreadystatechange = function() {
		if (this.readyState == 4 && this.status == 200) {
		shows = JSON.parse(this.responseText);
		updateshows();
		}
	};
	xhttp.open("GET", "/showcache.json?t="+Date.now(), true);
	xhttp.send();

</script>

<?php
do_action('storefront_sidebar');
get_footer();
