/*
 * A JavaScript implementation of the RSA Data Security, Inc. MD5 Message
 * Digest Algorithm, as defined in RFC 1321.
 * Version 2.2 Copyright (C) Paul Johnston 1999 - 2009
 * Other contributors: Greg Holt, Andrew Kepert, Ydnar, Lostinet
 * Distributed under the BSD License
 * See http://pajhome.org.uk/crypt/md5 for more info.
 */
var hexcase=0;var b64pad="";
function hex_md5(a){return rstr2hex(rstr_md5(str2rstr_utf8(a)))}
function b64_md5(a){return rstr2b64(rstr_md5(str2rstr_utf8(a)))}
function any_md5(a,b){return rstr2any(rstr_md5(str2rstr_utf8(a)),b)}
function hex_hmac_md5(a,b){return rstr2hex(rstr_hmac_md5(str2rstr_utf8(a),str2rstr_utf8(b)))}
function b64_hmac_md5(a,b){return rstr2b64(rstr_hmac_md5(str2rstr_utf8(a),str2rstr_utf8(b)))}
function any_hmac_md5(a,b,c){return rstr2any(rstr_hmac_md5(str2rstr_utf8(a),str2rstr_utf8(b)),c)}
function hexMD5(a){return hex_md5(a)}
function md5_vm_test(){return hex_md5("abc").toLowerCase()=="900150983cd24fb0d6963f7d28e17f72"}
function rstr_md5(a){return binl2rstr(binl_md5(rstr2binl(a),a.length*8))}
function rstr_hmac_md5(a,b){var c=rstr2binl(a);if(c.length>16)c=binl_md5(c,a.length*8);var d=Array(16),e=Array(16);for(var f=0;f<16;f++){d[f]=c[f]^909522486;e[f]=c[f]^1549556828}var g=binl_md5(d.concat(rstr2binl(b)),512+b.length*8);return binl2rstr(binl_md5(e.concat(g),512+16*32))}
function rstr2hex(a){try{hexcase}catch(b){hexcase=0}var c=hexcase?"0123456789ABCDEF":"0123456789abcdef";var d="";var e;for(var f=0;f<a.length;f++){e=a.charCodeAt(f);d+=c.charAt((e>>>4)&15)+c.charAt(e&15)}return d}
function rstr2b64(a){try{b64pad}catch(b){b64pad=""}var c="ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/";var d="";var e=a.length;for(var f=0;f<e;f+=3){var g=(a.charCodeAt(f)<<16)|(f+1<e?a.charCodeAt(f+1)<<8:0)|(f+2<e?a.charCodeAt(f+2):0);for(var h=0;h<4;h++){if(f*8+h*6>a.length*8)d+=b64pad;else d+=c.charAt((g>>>6*(3-h))&63)}}return d}
function rstr2any(a,b){var c=b.length;var d=[];var e,f,g,h,i;var j=Array(Math.ceil(a.length/2));for(i=0;i<j.length;i++){j[i]=(a.charCodeAt(i*2)<<8)|a.charCodeAt(i*2+1)}while(j.length>0){d.push(j[j.length-1]%c);j=rstr2any_divide(j,c)}var k="";for(i=0;i<b.length;i++)k+=b[0];for(i=0;i<d.length;i++)k+=b[d[d.length-i-1]];return k}
function rstr2any_divide(a,b){var c=Array();var d=0;for(var e=0;e<a.length;e++){var f=d*65536+a[e];c.push(Math.floor(f/b));d=f%b}if(c.length>0&&c[0]==0)c.shift();return c}
function str2rstr_utf8(a){var b="";var c=-1;var d,e;while(++c<a.length){d=a.charCodeAt(c);e=c+1<a.length?a.charCodeAt(c+1):0;if(55296<=d&&d<=56319&&56320<=e&&e<=57343){d=65536+((d-55296)<<10)+(e-56320);c++}if(d<=127)b+=String.fromCharCode(d);else if(d<=2047)b+=String.fromCharCode(192|((d>>>6)&31),128|(d&63));else if(d<=65535)b+=String.fromCharCode(224|((d>>>12)&15),128|((d>>>6)&63),128|(d&63));else b+=String.fromCharCode(240|((d>>>18)&7),128|((d>>>12)&63),128|((d>>>6)&63),128|(d&63))}return b}
function str2rstr_utf16le(a){var b="";for(var c=0;c<a.length;c++)b+=String.fromCharCode(a.charCodeAt(c)&255,(a.charCodeAt(c)>>>8)&255);return b}
function str2rstr_utf16be(a){var b="";for(var c=0;c<a.length;c++)b+=String.fromCharCode((a.charCodeAt(c)>>>8)&255,a.charCodeAt(c)&255);return b}
function rstr2binl(a){var b=Array(a.length>>2);for(var c=0;c<b.length;c++)b[c]=0;for(var c=0;c<a.length*8;c+=8)b[c>>5]|=(a.charCodeAt(c/8)&255)<<(c%32);return b}
function binl2rstr(a){var b="";for(var c=0;c<a.length*32;c+=8)b+=String.fromCharCode((a[c>>5]>>>(c%32))&255);return b}
function binl_md5(a,b){a[b>>5]|=128<<((b)%32);a[(((b+64)>>>9)<<4)+14]=b;var c=1732584193;var d=-271733879;var e=-1732584194;var f=271733878;for(var g=0;g<a.length;g+=16){var h=c;var i=d;var j=e;var k=f;c=md5_ff(c,d,e,f,a[g+0],7,-680876936);f=md5_ff(f,c,d,e,a[g+1],12,-389564586);e=md5_ff(e,f,c,d,a[g+2],17,606105819);d=md5_ff(d,e,f,c,a[g+3],22,-1044525330);c=md5_ff(c,d,e,f,a[g+4],7,-176418897);f=md5_ff(f,c,d,e,a[g+5],12,1200080426);e=md5_ff(e,f,c,d,a[g+6],17,-1473231341);d=md5_ff(d,e,f,c,a[g+7],22,-45705983);c=md5_ff(c,d,e,f,a[g+8],7,1770035416);f=md5_ff(f,c,d,e,a[g+9],12,-1958414417);e=md5_ff(e,f,c,d,a[g+10],17,-42063);d=md5_ff(d,e,f,c,a[g+11],22,-1990404162);c=md5_ff(c,d,e,f,a[g+12],7,1804603682);f=md5_ff(f,c,d,e,a[g+13],12,-40341101);e=md5_ff(e,f,c,d,a[g+14],17,-1502002290);d=md5_ff(d,e,f,c,a[g+15],22,1236535329);c=md5_gg(c,d,e,f,a[g+1],5,-165796510);f=md5_gg(f,c,d,e,a[g+6],9,-1069501632);e=md5_gg(e,f,c,d,a[g+11],14,643717713);d=md5_gg(d,e,f,c,a[g+0],20,-373897302);c=md5_gg(c,d,e,f,a[g+5],5,-701558691);f=md5_gg(f,c,d,e,a[g+10],9,38016083);e=md5_gg(e,f,c,d,a[g+15],14,-660478335);d=md5_gg(d,e,f,c,a[g+4],20,-405537848);c=md5_gg(c,d,e,f,a[g+9],5,568446438);f=md5_gg(f,c,d,e,a[g+14],9,-1019803690);e=md5_gg(e,f,c,d,a[g+3],14,-187363961);d=md5_gg(d,e,f,c,a[g+8],20,1163531501);c=md5_gg(c,d,e,f,a[g+13],5,-1444681467);f=md5_gg(f,c,d,e,a[g+2],9,-51403784);e=md5_gg(e,f,c,d,a[g+7],14,1735328473);d=md5_gg(d,e,f,c,a[g+12],20,-1926607734);c=md5_hh(c,d,e,f,a[g+5],4,-378558);f=md5_hh(f,c,d,e,a[g+8],11,-2022574463);e=md5_hh(e,f,c,d,a[g+11],16,1839030562);d=md5_hh(d,e,f,c,a[g+14],23,-35309556);c=md5_hh(c,d,e,f,a[g+1],4,-1530992060);f=md5_hh(f,c,d,e,a[g+4],11,1272893353);e=md5_hh(e,f,c,d,a[g+7],16,-155497632);d=md5_hh(d,e,f,c,a[g+10],23,-1094730640);c=md5_hh(c,d,e,f,a[g+13],4,681279174);f=md5_hh(f,c,d,e,a[g+0],11,-358537222);e=md5_hh(e,f,c,d,a[g+3],16,-722521979);d=md5_hh(d,e,f,c,a[g+6],23,76029189);c=md5_hh(c,d,e,f,a[g+9],4,-640364487);f=md5_hh(f,c,d,e,a[g+12],11,-421815835);e=md5_hh(e,f,c,d,a[g+15],16,530742520);d=md5_hh(d,e,f,c,a[g+2],23,-995338651);c=md5_ii(c,d,e,f,a[g+0],6,-198630844);f=md5_ii(f,c,d,e,a[g+7],10,1126891415);e=md5_ii(e,f,c,d,a[g+14],15,-1416354905);d=md5_ii(d,e,f,c,a[g+5],21,-57434055);c=md5_ii(c,d,e,f,a[g+12],6,1700485571);f=md5_ii(f,c,d,e,a[g+3],10,-1894986606);e=md5_ii(e,f,c,d,a[g+10],15,-1051523);d=md5_ii(d,e,f,c,a[g+1],21,-2054922799);c=md5_ii(c,d,e,f,a[g+8],6,1873313359);f=md5_ii(f,c,d,e,a[g+15],10,-30611744);e=md5_ii(e,f,c,d,a[g+6],15,-1560198380);d=md5_ii(d,e,f,c,a[g+13],21,1309151649);c=md5_ii(c,d,e,f,a[g+4],6,-145523070);f=md5_ii(f,c,d,e,a[g+11],10,-1120210379);e=md5_ii(e,f,c,d,a[g+2],15,718787259);d=md5_ii(d,e,f,c,a[g+9],21,-343485551);c=safe_add(c,h);d=safe_add(d,i);e=safe_add(e,j);f=safe_add(f,k)}return Array(c,d,e,f)}
function md5_cmn(a,b,c,d,e,f){return safe_add(bit_rol(safe_add(safe_add(b,a),safe_add(d,f)),e),c)}
function md5_ff(a,b,c,d,e,f,g){return md5_cmn((b&c)|((~b)&d),a,b,e,f,g)}
function md5_gg(a,b,c,d,e,f,g){return md5_cmn((b&d)|(c&(~d)),a,b,e,f,g)}
function md5_hh(a,b,c,d,e,f,g){return md5_cmn(b^c^d,a,b,e,f,g)}
function md5_ii(a,b,c,d,e,f,g){return md5_cmn(c^(b|(~d)),a,b,e,f,g)}
function safe_add(a,b){var c=(a&65535)+(b&65535);var d=(a>>16)+(b>>16)+(c>>16);return(d<<16)|(c&65535)}
function bit_rol(a,b){return(a<<b)|(a>>>(32-b))}
