// Not loaded by any page. This entry exists only so Vite's build visits these
// `?url` imports, which copies each legacy vendor file to public/build/assets
// with a content hash and records it in the manifest - so Vite::asset() can
// resolve it for a literal <script src> tag in the shared layouts. `?url`
// means Vite treats the file as a raw asset to copy, not JavaScript to parse
// and bundle: these are global-script/UMD libraries, not ES modules, and
// parsing them as JS makes Rollup wrongly treat their UMD "is this
// CommonJS?" branch as real CommonJS (it calls require(), which doesn't
// exist in the build output).
import '../../vendor-src/jquery-3.3.1.min.js?url';
import '../../vendor-src/bootstrap/bootstrap.bundle.js?url';
import '../../vendor-src/jquery.slimscroll.js?url';
import '../../vendor-src/charts/chartist.min.js?url';
import '../../vendor-src/jquery.sparkline.js?url';
import '../../vendor-src/charts/raphael.min.js?url';
import '../../vendor-src/charts/morris.js?url';
import '../../vendor-src/charts/c3.min.js?url';
import '../../vendor-src/charts/d3-5.4.0.min.js?url';
import '../../vendor-src/charts/C3chartjs.js?url';
import '../../vendor-src/charts/Chart.bundle.js?url';
import '../../vendor-src/charts/chartjs-demo.js?url';
