/*
MIT License

Copyright (c) 2017 Andrew S (Andrews54757_at_gmail.com)

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
SOFTWARE.
*/


var version = "1.0.1";

var today = new Date();
var dd = today.getDate();
var mm = today.getMonth() + 1; //January is 0!

var yyyy = today.getFullYear();
if (dd < 10) {
    dd = '0' + dd;
}
if (mm < 10) {
    mm = '0' + mm;
}
var date = dd + '/' + mm + '/' + yyyy;

function removeComments(str) {

    str = str.split("");
    var len = str.length;
    var out = [];
    var i = 0

    function skip(match) {

        var backslash = false;
        out.push(match);

        for (++i; i < len; i++) {
            char = str[i];
            out.push(char);
            if (char === "\\") backslash = true;
            else if (char === match && !backslash) {
                break;
            } else if (backslash) {
                backslash = false;
            }
        }
    }
    for (; i < len; i++) {
        var char = str[i];

        if (char == "\"") {
            skip("\"");
        } else if (char == "'") {
            skip("'");
        } else if (char == "/") {
            i++
            if (str[i] == "/") {
                for (++i; i < len; i++) {
                    var c = str[i];
                    if (c == "\n") {
                        out.push("\n")
                        break;
                    }
                }

            } else if (str[i] == "*") {

                var star = false;
                for (++i; i < len; i++) {
                    var c = str[i];
                    if (c == "*") {
                        star = true;
                    } else
                    if (c == "/" && star) {
                        break;
                    } else if (star) {
                        star = false;
                    }
                }
            }
        } else {
            out.push(char);
        }

    }
    return out.join('').replace(/\n\s*\n/g, "\n");
}

// Minifier -> https://github.com/ThreeLetters/PHP-Minify-js
function minify(str,options) {
    if (!options) options = {};
    str = removeComments(str);
    var varReplace = options.varReplace === undefined ? true : options.varReplace;
    var extreme = options.extremeMinify === undefined ? true : options.extremeMinify;
    var removeLine = options.removeLine === undefined ? true : options.removeLine;
    
    if (removeLine) {
        str = str.replace(/\n/g, "").replace(/\t/g,"").split("");
    } else {
       str = str.split("");
    }
    var len = str.length;
    var out = [];
    var i = 0;
    var varIndex = [0];
    var varChars = ["a", "b", "c", "d", "e", "f", "g", "h", "i", "j", "k", "l", "m", "n", "o", "p", "q", "r", "s", "t", "u", "v", "w", "x", "y", "z"];
    var varLen = varChars.length;
    var varMap = {};

    function getNextVar() {

        var str = "";
        var j = 0;
        while (true) {
            if (!varIndex[j]) varIndex[j] = 0;
            varIndex[j]++;

            if (varIndex[j] > varLen) {
                varIndex[j] = 1;
            } else {
                break;
            }
            j++;
        }

        for (var i = 0; i < varIndex.length; i++) {
            str += varChars[varIndex[i] - 1];
        }

        return str;
    }

    function skip(match) {

        var backslash = false;
        out.push(match);

        for (++i; i < len; i++) {
            char = str[i];
            out.push(char);
            if (char === "\\") backslash = true;
            else if (char === match && !backslash) {
                break;
            } else if (backslash) {
                backslash = false;
            }
        }
    }

    function includes(char) {

        var dt = [";", "{", "}", ",", "(", ")", "[", "]", "=", ">", "<", "."];
        if (extreme) dt.push("&","|","+","-","*","/");
        return dt.indexOf(char) != -1;
    }

    function includes2(char) {
        var dt = ["=", "{", "(", "}", ")", "]", ">", "<", "!", "."];
        if (extreme) dt.push("$","&","|","+","-","*","/");
        return dt.indexOf(char) != -1;
    }

    function includes3(char) {
        var dt = ["_", "1", "2", "3", "4", "5", "6", "7", "8", "9", "a", "b", "c", "d", "e", "f", "g", "h", "i", "j", "k", "l", "m", "n", "o", "p", "q", "r", "s", "t", "u", "v", "w", "x", "y", "z"];

        return dt.indexOf(char.toLowerCase()) != -1;
    }

    for (; i < len; i++) {
        var char = str[i];

        if (char == "\"") {
            skip("\"");
        } else if (char == "'") {
            skip("'");
        } else if (char == "`") {
            skip("`");
        } else if (varReplace && char == "$") {

            var v = "";
            for (; i < len; i++) {

                if (!includes3(str[i + 1])) break;
                v += str[i + 1];
            }
            if (varMap[v]) {
                out.push(varMap[v]);
            } else if (v != "this") {
                var found = false;
                for (var j = i; j < len; j++) {
                    if (str[j + 1] == "=" || str[j + 1] == "," || str[j + 1] == ")") {
                        found = true;
                        break;
                    } else if (str[j + 1] != " ") {
                        break;
                    }
                }
                if (found) {
                    for (var j = i - v.length - 1; j > 0; j--) {
                        if (str[j] == "c" || str[j] == "e" || str[j] == "l" || str[j] == "d") {
                            found = false;
                            break;
                        } else if (str[j] != " ") {
                            break;
                        }
                    }
                }
                if (found) {
                    varMap[v] = "$" + getNextVar();
                    // console.log(v, varMap[v])
                    out.push(varMap[v]);
                } else {
                    out.push("$" + v);
                }
            } else {
                out.push("$" + v);
            }
            //console.log(v);

        } else if (char == " ") {

            var d = true;

            for (; i < len; i++) {

                if (includes2(str[i + 1])) {
                    d = false;
                }
                if (str[i + 1] != " ") break;
            }
            if (d) out.push(" ");

        } else if (includes(char)) {
            out.push(char);
            for (; i < len; i++) {
                if (str[i + 1] != " ") break;
            }
        } else {
            out.push(char)
        }

    }
    return out.join("");
}

var fs = require("fs");

var simple = fs.readFileSync(__dirname + "/lib/parser/Simple.php", "utf8");

var adv = fs.readFileSync(__dirname + "/lib/parser/Advanced.php", "utf8");

var connector = fs.readFileSync(__dirname + "/lib/connector/index.php", "utf8");

var main = fs.readFileSync(__dirname + "/index.php", "utf8");

var helper = fs.readFileSync(__dirname + "/lib/helper/index.php", "utf8");

var startstr = "// BUILD BETWEEN";



simple = removeComments(simple.split(startstr)[1]);
adv = removeComments(adv.split(startstr)[1]);
connector = removeComments(connector.split(startstr)[1]);
var index = main.split(startstr)[1];
main = removeComments(index);
helper = removeComments(helper.split(startstr)[1]);

var out = `<?php\n\
/*\n\
 Author: Andrews54757\n\
 License: MIT (https://github.com/ThreeLetters/SuperSQL/blob/master/LICENSE)\n\
 Source: https://github.com/ThreeLetters/SQL-Library\n\
 Build: v${version}\n\
 Built on: ${date}\n\
*/\n\n`;

var complete = `// lib/connector/index.php\
${connector}\n\
// lib/parser/Simple.php\
${simple}\n\
// lib/parser/Advanced.php\
${adv}\n\
// index.php\
${main}\
?>`;

var completeMin = `// lib/connector/index.php\n\
${minify(connector)}\n\
// lib/parser/Simple.php\n\
${minify(simple)}\n\
// lib/parser/Advanced.php\n\
${minify(adv)}\n\
// index.php\n\
${minify(main)}\n\
?>`;

var smain = index.split("// BUILD ADVANCED BETWEEN");
smain = (smain[0] + smain[2]);
smain = removeComments(smain);
var simpleOnly = `// lib/connector/index.php\
${connector}\n\
// lib/parser/Simple.php\
${simple}\n\
// index.php\
${smain}\
?>`;
var simpleOnlyMin = `// lib/connector/index.php\n\
${minify(connector)}\n\
// lib/parser/Simple.php\n\
${minify(simple)}\n\
// index.php\n\
${minify(smain)}\n\
?>`;

var amain = index.split("// BUILD SIMPLE BETWEEN");
amain = (amain[0] + amain[2]);
amain = removeComments(amain);
var advancedOnly = `// lib/connector/index.php\
${connector}\n\
// lib/parser/Advanced.php\
${adv}\n\
// index.php\
${amain}\
?>`;

var advancedOnlyMin = `// lib/connector/index.php\n\
${minify(connector)}\n\
// lib/parser/Advanced.php\n\
${minify(adv)}\n\
// index.php\n\
${minify(amain)}\n\
?>`;

var a = out + complete,
    b = out + completeMin,
    c = out + simpleOnly,
    d = out + simpleOnlyMin,
    e = out + advancedOnly,
    f = out + advancedOnlyMin,
    g = out + helper,
    h = out + minify(helper) + "\n?>";


fs.writeFileSync(__dirname + "/dist/SuperSQL.php", a);
fs.writeFileSync(__dirname + "/dist/SuperSQL_min.php", b);


fs.writeFileSync(__dirname + "/dist/SuperSQL_simple.php", c);
fs.writeFileSync(__dirname + "/dist/SuperSQL_simple_min.php", d);


fs.writeFileSync(__dirname + "/dist/SuperSQL_advanced.php", e);
fs.writeFileSync(__dirname + "/dist/SuperSQL_advanced_min.php", f);


fs.writeFileSync(__dirname + "/dist/SuperSQL_helper.php", g);
fs.writeFileSync(__dirname + "/dist/SuperSQL_helper_min.php", h);


console.log("Compiled files into dist. Stats:");

console.log("OUTPUT");

console.log(`SuperSQL: ~${a.length} Lines: ~${a.split("\n").length} - Minified: ~${b.length} Lines: ~${b.split("\n").length}`);
console.log(`Simple: ~${c.length} Lines: ~${c.split("\n").length} - Minified: ~${d.length} Lines: ~${d.split("\n").length}`);
console.log(`Advanced: ~${e.length} Lines: ~${e.split("\n").length} - Minified: ~${f.length} Lines: ~${f.split("\n").length}`);
console.log(`Helper: ~${g.length} Lines: ~${g.split("\n").length} - Minified: ~${h.length} Lines: ~${h.split("\n").length}`);

console.log("FILES");

console.log(`Index ${index.length} Lines: ~${index.split("\n").length}`);
console.log(`Connector ${connector.length} Lines: ~${connector.split("\n").length}`);
console.log(`SimpleParser ${simple.length} Lines: ~${simple.split("\n").length}`);
console.log(`AdvancedParser ${adv.length} Lines: ~${adv.split("\n").length}`);
