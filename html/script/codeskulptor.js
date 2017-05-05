var errorLine = null;
var errorLineNo = -1;


/**
 * http://stackoverflow.com/questions/5537622/dynamically-loading-css-file-using-javascript-with-callback-without-jquery
 */
function loadStyleSheet( path, fn, scope ) {
   var head = document.getElementsByTagName( 'head' )[0], // reference to document.head for appending/ removing link nodes
       link = document.createElement( 'link' );           // create the link node
   link.setAttribute( 'href', path );
   link.setAttribute( 'rel', 'stylesheet' );
   link.setAttribute( 'type', 'text/css' );
   var sheet, cssRules;
   if ( 'sheet' in link ) {
      sheet = 'sheet'; cssRules = 'cssRules';
   }
   else {
      sheet = 'styleSheet'; cssRules = 'rules';
   }
   var interval_id = setInterval( function() {                     // start checking whether the style sheet has successfully loaded
          try {
             if ( link[sheet] && link[sheet][cssRules].length ) { // SUCCESS! our style sheet has loaded
                clearInterval( interval_id );                      // clear the counters
                clearTimeout( timeout_id );
                fn.call( scope || window, true, link );           // fire the callback with success == true
             }
          } catch( e ) {} finally {}
       }, 10 ),                                                   // how often to check if the stylesheet is loaded
       timeout_id = setTimeout( function() {       // start counting down till fail
          clearInterval( interval_id );             // clear the counters
          clearTimeout( timeout_id );
          head.removeChild( link );                // since the style sheet didn't load, remove the link node from the DOM
          fn.call( scope || window, false, link ); // fire the callback with success == false
       }, 8000 );                                 // how long to wait before failing

   head.appendChild( link );  // insert the link node into the DOM and start loading the style sheet
   return link; // return the link node;
}

function log(element, text) {
    element.innerHTML = element.innerHTML + text;
}

function printOutput(text) {
    log( document.getElementById("output"), text);
}

function readBuiltInFile(file) {
    if (Sk.builtinFiles === undefined || Sk.builtinFiles["files"][file] === undefined)
        throw "File not found: '" + file + "'";
    return Sk.builtinFiles["files"][file];
}

function printError(text) {
    log( document.getElementById("debugout"), text);
}

function runCode() {
    var prog = editor.getValue();
    reset();
    Sk.pre = "output";
    Sk.configure({
        output: printOutput,
        read: readBuiltInFile,
        error: printError
    });
    printOutput("Starting script...\n");
    var start = new Date().getTime();
    try {
        Sk.importMainWithBody("<stdin>", false, prog);
    }
    catch(e) {
        if (e instanceof Sk.builtin.ParseError || e instanceof Sk.builtin.SyntaxError || e instanceof Sk.builtin.IndentationError || e instanceof Sk.builtin.TokenError) {
            try {
                if (e.args.v[2] !== undefined) {
                    Sk.currLineNo = e.args.v[2]
                }
                if (e.args.v[1] !== undefined) {
                    Sk.currFilename = e.args.v[1].v
                }
                var t = e.args.v[3][0][1];
                var r = e.args.v[3][1][1];
                var o = e.args.v[3][2].substring(t, r);
                e.args.v[0] = e.args.v[0].sq$concat(new Sk.builtin.str(" ('" + o + "')"))
            } catch (x) {}
        }
        var i = "On line " + e.lineno + ": " + e.tp$name + ": " + e;
        printError(i);

        var n = (Sk.currLineNo);

        if(n) {
	        errorLine = editor.addLineClass(n - 1, "background", "activeline");
	        errorLineNo = n;
	        editor.setCursor(n - 1);
	        editor.focus();
        }

        if (Sk.simplegui) {
            Sk.simplegui.cleanup();
            Sk.simplegui = undefined
        }
        if (Sk.simpleplot) {
            Sk.simpleplot.cleanup();
            Sk.simpleplot = undefined
        }
        if (Sk.maps) {
            Sk.maps.cleanup();
            Sk.maps = undefined
        }
    }

    var end = new Date().getTime();
    var time = end - start;
    printOutput("Finished executing script after " + time + " milliseconds\n");
}

function reset() {
    var mypre = document.getElementById("output");
    mypre.innerHTML = '';
    var mypre = document.getElementById("debugout");
    mypre.innerHTML = '';
    if(errorLine)
    	editor.removeLineClass(errorLine, "background", "activeline");
    errorLineNo = -1;
}

$(function() {
    $('#run-button').click(function() {
        runCode();
    });
});

function foldFunc(cm, pos) {
    editor.foldCode(pos, {
        rangeFinder: CodeMirror.fold.indent,
        scanUp: true
    });
}

function changed(cm, obj) {
	if(errorLineNo >= 0) {
		if(editor.getCursor().line !== errorLineNo) {
			return;
		}
    	editor.removeLineClass(errorLine, "background", "activeline");
	}
}

function getCookie(cname) {
    var name = cname + "=";
    var decodedCookie = decodeURIComponent(document.cookie);
    var ca = decodedCookie.split(';');
    for(var i = 0; i <ca.length; i++) {
        var c = ca[i];
        while (c.charAt(0) == ' ') {
            c = c.substring(1);
        }
        if (c.indexOf(name) == 0) {
            return c.substring(name.length, c.length);
        }
    }
    return "";
}

theme = getCookie("scripttheme");
if(!theme){theme = "monokai";}

var editor = CodeMirror.fromTextArea(document.getElementById("code"), {
    mode: {
        name: "python",
        version: 2,
        singleLineStringErrors: false
    },
    gutters: ["fold-gutter", "CodeMirror-gutter"],
    lineNumbers: true,
    indentUnit: 4,
    tabMode: "indent",
    matchBrackets: true,
    theme: theme,
    extraKeys: {
        "Ctrl-R": runCode
    }
});

editor.on("gutterClick", foldFunc);
editor.on("change", changed);