var version = "2.0.0";

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
    str = ('__' + str + '__').split('');
    var mode = {
        singleQuote: false,
        doubleQuote: false,
        regex: false,
        blockComment: false,
        lineComment: false,
        condComp: false 
    };
    for (var i = 0, l = str.length; i < l; i++) {
 
        if (mode.regex) {
            if (str[i] === '/' && str[i-1] !== '\\') {
                mode.regex = false;
            }
            continue;
        }
 
        if (mode.singleQuote) {
            if (str[i] === "'" && str[i-1] !== '\\') {
                mode.singleQuote = false;
            }
            continue;
        }
 
        if (mode.doubleQuote) {
            if (str[i] === '"' && str[i-1] !== '\\') {
                mode.doubleQuote = false;
            }
            continue;
        }
 
        if (mode.blockComment) {
            if (str[i] === '*' && str[i+1] === '/') {
                str[i+1] = '';
                mode.blockComment = false;
            }
            str[i] = '';
            continue;
        }
 
        if (mode.lineComment) {
            if (str[i+1] === 'n' || str[i+1] === 'r') {
                mode.lineComment = false;
            }
            str[i] = '';
            continue;
        }
 /*
        if (mode.condComp) {
            if (str[i-2] === '@' && str[i-1] === '*' && str[i] === '/') {
                mode.condComp = false;
            }
            continue;
        }
 */
        mode.doubleQuote = str[i] === '"';
        mode.singleQuote = str[i] === "'";
 
        if (str[i] === '/') {
            /*
            if (str[i+1] === '*' && str[i+2] === '@') {
                mode.condComp = true;
                continue;
            }
            */
            if (str[i+1] === '*') {
                str[i] = '';
                mode.blockComment = true;
                continue;
            }
            if (str[i+1] === '/') {
                str[i] = '';
                mode.lineComment = true;
                continue;
            }
            mode.regex = true;
 
        }
 
    }
    
    return str.join('').slice(2, -2).replace(/\n\s*\n/g,"\n");
}
            
var fs = require("fs");

var simple = fs.readFileSync(__dirname + "/lib/parser/Simple.php", "utf8");

var adv = fs.readFileSync(__dirname + "/lib/parser/Advanced.php", "utf8");

var connector = fs.readFileSync(__dirname + "/lib/connector/index.php", "utf8");

var main = fs.readFileSync(__dirname + "/index.php", "utf8");

var startstr = "// BUILD BETWEEN";



simple = removeComments(simple.split(startstr)[1]);
adv = removeComments(adv.split(startstr)[1]);
connector = removeComments(connector.split(startstr)[1]);
main = removeComments(main.split(startstr)[1]);

var out = `<?php\n\
/*\n\
MIT License\
\n\
Copyright (c) 2017 Andrew S\n\
\n\
Permission is hereby granted, free of charge, to any person obtaining a copy\n\
of this software and associated documentation files (the "Software"), to deal\n\
in the Software without restriction, including without limitation the rights\n\
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell\n\
copies of the Software, and to permit persons to whom the Software is\n\
furnished to do so, subject to the following conditions:\n\
\n\
The above copyright notice and this permission notice shall be included in all\n\
copies or substantial portions of the Software.\n\
\n\
THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR\n\
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,\n\
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE\n\
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER\n\
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,\n\
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE\n\
SOFTWARE.\n\
\n\
*/\n\
\n\
/*\n\
 Author: Andrews54757\n\
 License: MIT\n\
 Source: https://github.com/ThreeLetters/SQL-Library\n\
 Build: v${version}\n\
 Built on: ${date}\n\
*/\n\n\
// lib/connector/index.php\
${connector}\n\
// lib/parser/Simple.php\
${simple}\n\
// lib/parser/Advanced.php\
${adv}\n\
// index.php\
${main}\
?>`;

fs.writeFileSync(__dirname + "/dist/SuperSQL.php", out);
