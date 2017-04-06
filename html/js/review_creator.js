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
	    	if($(this).parent().index() == 2) {
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

	const category = '<tr><td class="editable">Platzhalter...</td><td class="editable">Platzhalter...</td><td class="editable">1</td><td class="delete-row"><i class="fa fa-trash red-text accent-4" aria-hidden="true"></i></td></tr>';
	const section = '<li class="sect"><span class="create_review_section editable">%section%</span><br><span class="badge green accent-4 create_category"><i class="fa fa-plus" aria-hidden="true"></i> Neue Kategorie </span><span class="badge red accent-4 delete_section"><i class="fa fa-trash-o" aria-hidden="true"></i> Abschnitt löschen </span><table class="table table-hover"><tr><th>Kategorie</th><th>Beschreibung</th><th>Maximale Punktzahl</th></tr></table></li>';

	function addCategory() {
		$(this).parent(".sect").find("table").append(category);
    	$(".editable").unbind().click(divClicked);
    	$('.delete-row').unbind().click(deleteCategory);
	}

	function deleteCategory() {
		$(this).parent("tr").remove();
	}

	function deleteSection() {
		$(this).parent("li").remove();
	}

	function addSection() {
		$('#create_review').append(section.replace("%section%", "Neuer Abschnitt"));
		$(".create_category").unbind().click(addCategory);
		$('#create_review .sect').last().find(".create_category").click();
		$('.delete_section').unbind().click(deleteSection);
	}

	$('.delete_section').unbind().click(deleteSection);
    $("#create_review > .sect > .create_category").unbind().click(addCategory);
    $('.delete-row').unbind().click(deleteCategory);
    $('.create_section').unbind().click(addSection);

});

