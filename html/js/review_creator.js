$(document).ready(function() {

	function isStringEmpty(str) {
	    return (str.length === 0 || !str.trim());
	}

	function divClicked() {
	    var divHtml = $(this).html();
	    var td = null;
	    if($(this).is("td")) {
	    	td = $("<td class=\"editable\">");
	    } else {
	    	td = $("<span class=\"create_review_section editable\">");
	    }
	    var editableText = $("<textarea />");
	    editableText.val(divHtml);
	    editableText.appendTo(td);
	    $(this).replaceWith(td);
	    editableText.focus();
	    // setup the blur event for this new textarea
	    editableText.blur(editableTextBlurred);
	    console.log("div clicked");
	}

	function editableTextBlurred() {
	    var html = $(this).val();
	    var viewableText = null;
	    if($(this).parent("td").length) {
	    	viewableText = $("<td>");
	    	if($(this).parent().index() == 1) {
		    	if(isNaN(html)) {
		    		html = 1;
		           	toastr.error('Ungültige Nummer!', "Fehler");
		    	} else {
		    		html = Math.max(1, parseInt(html));
		    	}
		    } else {
		    	if(isStringEmpty(html)) {
		    		html = "Platzhalter...";
		    	}
		    }
	    } else {
	    	viewableText = $("<span class=\"create_review_section editable\">");
	    }
	    
	    viewableText.html(html);
	    $(this).parent().replaceWith(viewableText);
	    // setup the click event for this new div
	    viewableText.click(divClicked);
	}

    $(".editable").unbind().click(divClicked);

	const category = '<tr class="data-row"><td class="editable">Platzhalter...</td><td class="editable">1</td><td class="delete-row"><i class="fa fa-trash red-text accent-4" aria-hidden="true"></i></td></tr>';
	const section = '<li class="creation_sect"><span class="create_review_section editable">%section%</span><br><span class="badge btn-success create_category"><i class="fa fa-plus" aria-hidden="true"></i> Neue Kategorie</span><span class="badge btn-danger delete_section"><i class="fa fa-trash-o" aria-hidden="true"></i> Abschnitt löschen</span><table class="table"><tr><th>Beschreibung</th><th>Maximale Punktzahl</th></tr></table></li>';

	function addCategory() {
		$(this).parent(".creation_sect").find("table").append(category);
    	$(".editable").unbind().click(divClicked);
    	$('.delete-row').unbind().click(deleteCategory);
	}

	function deleteCategory() {
		$(this).parent("tr").remove();
	}

	function deleteSection() {
		$(this).parent("li").remove();
	}

	function getQueryParams(qs) {
	    qs = qs.split('+').join(' ');

	    var params = {},
	        tokens,
	        re = /[?&]?([^=]+)=([^&]*)/g;

	    while (tokens = re.exec(qs)) {
	        params[decodeURIComponent(tokens[1])] = decodeURIComponent(tokens[2]);
	    }

	    return params;
	}

	function createReview() {
		console.log("Trying to create a new review");
		const length = $('.creation_sect').length;
		console.log("There are " + length + " sections");
		var arr = {};
		arr['name'] = $('#create_review_name').val();

		if( isStringEmpty(arr['name'] )) {
			toastr.error("Bitte gib einen gültigen Namen für das Review ein!", 'Fehler!');
			$('#create_review_name').focus();
			return;
		}

		$('.creation_sect').each(function (index) {
			const section = $(this);
			const name = section.find('.create_review_section').html();
			console.log("name = " + name);
			const categoryLength = section.find('.data-row').length;

			console.log("Section number " + index + " has " + categoryLength + " categories.")
			arr['name_' + index] = name;
			const i = index;
			for (var j = 0; j < categoryLength; j++) {
				section.find('.data-row:eq('+ (j) + ') td').not('.delete-row').each( function (index) {
					const value = $(this).html();
					arr['cat_' + i + "_" + j + "_" + index] = value;
				});
			}
		});
			

		arr['course'] = getQueryParams(document.location.search).course;
		console.log("Got resulting array: \n");
		console.log(arr);

		console.log("Stringified JSON: " + JSON.stringify(arr));
		const url = "create_review.php";
		console.log("Connecting to " + url);

	    $.ajax({ 
	        url: url,
	        data: arr,
	        type: 'post',
	        success: function(result) {
	        	console.log("Got result " + result);
	           	toastr.success('Review wurde erfolgreich erstellt!', 'Geschafft!');
	        },
	        error: function(error) {
	           	toastr.error(error, 'Fehler!');
	        }
        });
	}

	function addSection() {
		$('#create_review').append(section.replace("%section%", "Neuer Abschnitt"));
		$(".create_category").unbind().click(addCategory);
		$('#create_review .creation_sect').last().find(".create_category").click();
		$('.delete_section').unbind().click(deleteSection);
	}

	$('.delete_section').unbind().click(deleteSection);
    $("#create_review > .creation_sect > .create_category").unbind().click(addCategory);
    $('.delete-row').unbind().click(deleteCategory);
    $('.create_section').unbind().click(addSection);
    $('#create_review_button').unbind().click(createReview);


 	$('.create_section').click();
});

