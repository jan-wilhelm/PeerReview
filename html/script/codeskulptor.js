var errorLine = null;
var errorLineNo = -1;

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
	console.log(cm, obj, errorLineNo);
	if(errorLineNo >= 0) {
		if(editor.getCursor().line !== errorLineNo) {
			return;
		}
    	editor.removeLineClass(errorLine, "background", "activeline");
	}
}

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
    theme: "monokai",
    extraKeys: {
        "Ctrl-R": runCode
    }
});

editor.on("gutterClick", foldFunc);
editor.on("change", changed);