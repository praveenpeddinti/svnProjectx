"use strict";
function __export(m) {
    for (var p in m) if (!exports.hasOwnProperty(p)) exports[p] = m[p];
}
// Import all pipes
var truncate_pipe_1 = require('./pipes/truncate.pipe');
// Export all pipes
__export(require('./pipes/truncate.pipe'));
// Export convenience property
exports.PIPES = [
    truncate_pipe_1.TruncatePipe
];
//# sourceMappingURL=pipes.js.map