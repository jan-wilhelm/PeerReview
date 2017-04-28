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
       printError(e.toString());
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
}

$(function() {
    $('#run-button').click(function() {
        runCode();
    });
});

function foldFunc(cm, pos) {
    
    var A1 = editor.getCursor().line;
    editor.foldCode(pos, {
        rangeFinder: CodeMirror.fold.indent,
        scanUp: true
    });
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