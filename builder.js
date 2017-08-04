var version = "1.0.0";

var today = new Date();
var dd = today.getDate();
var mm = today.getMonth()+1; //January is 0!

var yyyy = today.getFullYear();
if(dd<10){
    dd='0'+dd;
} 
if(mm<10){
    mm='0'+mm;
} 
var date = dd+'/'+mm+'/'+yyyy;


var fs = require("fs");

var parser = fs.readFileSync(__dirname + "/lib/parser/index.php","utf8");

var connector = fs.readFileSync(__dirname + "/lib/connector/index.php","utf8");

var main = fs.readFileSync(__dirname + "/index.php","utf8");


var startstr = "// BUILD BETWEEN";
parser = parser.split(startstr)[1];
connector = parser.split(startstr)[1];
main = parser.split(startstr)[1];

var out = `/*\
MIT License\
\
Copyright (c) 2017 Andrew S\
\
Permission is hereby granted, free of charge, to any person obtaining a copy\
of this software and associated documentation files (the "Software"), to deal\
in the Software without restriction, including without limitation the rights\
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell\
copies of the Software, and to permit persons to whom the Software is\
furnished to do so, subject to the following conditions:\
\
The above copyright notice and this permission notice shall be included in all\
copies or substantial portions of the Software.\
\
THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR\
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,\
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE\
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER\
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,\
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE\
SOFTWARE.\
\
*/\
\
/*\
 Author: Andrews54757\
 License: MIT\
 Source: https://github.com/ThreeLetters/SQL-Library\
 Build: v${version}\
 Built on: ${date}\
*/\
\
${connector}\
${parser}\
${main}`;

fs.writeFileSync(__dirname + "/dist/SuperSQL.php",out);


