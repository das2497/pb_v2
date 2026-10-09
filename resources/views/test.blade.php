<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Excel Export Test - Nativefier Debug Version</title>
        <style>
    body {
      font-family: Arial, Helvetica, sans-serif;
      padding: 30px;
      background: #f9f9f9;
      margin: 0;
    }
    h1 { color: #333; }
    button {
      padding: 12px 28px;
      font-size: 16px;
      background: #0066cc;
      color: white;
      border: none;
      border-radius: 6px;
      cursor: pointer;
      margin-bottom: 20px;
    }
    button:hover { background: #0055aa; }
    table {
      width: 100%;
      border-collapse: collapse;
      background: white;
      box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }
    th, td {
      padding: 12px 15px;
      text-align: left;
      border: 1px solid #ccc;
    }
    th {
      background-color: #0066cc;
      color: white;
    }
    tr:nth-child(even) { background-color: #f2f2f2; }
    tr:hover { background-color: #e8f4fc; }
    #status { margin-top: 10px; color: #666; }
  </style>
    </head>
    <body>

        <h1>Nativefier Excel Export — Debug Mode</h1>
        <p>Open DevTools (right-click → Inspect) and watch the Console for
            logs.</p>
        <p><strong>Tip:</strong> Check your Downloads folder first—files might
            download silently!</p>

        <button onclick="exportToExcel()">Export Table to Excel (.xls)</button>
        <div id="status"></div>

        <table id="dataTable">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Age</th>
                    <th>Country</th>
                    <th>Position</th>
                    <th>Salary ($)</th>
                </tr>
            </thead>
            <tbody>
                <tr><td>Alice
                        Johnson</td><td>29</td><td>Canada</td><td>Engineer</td><td>105000</td></tr>
                <tr><td>Carlos
                        López</td><td>35</td><td>Mexico</td><td>Designer</td><td>88000</td></tr>
                <tr><td>Sofia
                        Müller</td><td>41</td><td>Germany</td><td>Manager</td><td>132000</td></tr>
                <tr><td>Raj
                        Patel</td><td>27</td><td>India</td><td>Developer</td><td>95000</td></tr>
                <tr><td>Emma
                        Dubois</td><td>33</td><td>France</td><td>Analyst</td><td>78000</td></tr>
            </tbody>
        </table>

        <script>
    function updateStatus(msg) {
      document.getElementById('status').innerText = msg;
      console.log('[Export Debug]: ' + msg);
    }

    function exportToExcel() {
      updateStatus('Starting export...');
      const table = document.getElementById("dataTable");

      // Build clean HTML with inline styles (works perfectly in Excel)
      let html = `
        <!DOCTYPE html>
        <html>
        <head>
          <meta charset="utf-8">
          <title>Exported Data</title>
          <style>
            table { border-collapse: collapse; width: 100%; }
            td, th { border: 1px solid #999; padding: 8px; text-align: left; }
            th { background-color: yellow; color: white; }
          </style>
        </head>
        <body>
          ${table.outerHTML}
        </body>
        </html>`;

      try {
        // Primary method: Blob + URL
        updateStatus('Creating blob...');
        const blob = new Blob([html], { type: "application/vnd.ms-excel" });
        updateStatus('Blob created. Generating URL...');
        const url = URL.createObjectURL(blob);        

        updateStatus('Triggering download...');
        const a = document.createElement("a");
        a.href = url;
        a.download = "Report-" + new Date().toISOString().slice(0,10) + ".xls";
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);

        updateStatus('Download successfully.');
        URL.revokeObjectURL(url);

        // Fallback: Data URI (if blob fails)
        // setTimeout(() => {
        //   if (confirm('Blob method might have failed silently. Try data URI fallback?')) {
        //     const dataUri = 'data:application/vnd.ms-excel;base64,' + btoa(unescape(encodeURIComponent(html)));
        //     const fallbackA = document.createElement("a");
        //     fallbackA.href = dataUri;
        //     fallbackA.download = "Report-Fallback.xls";
        //     document.body.appendChild(fallbackA);
        //     fallbackA.click();
        //     document.body.removeChild(fallbackA);
        //     updateStatus('Fallback attempted.');
        //   }
        // }, 500);

      } catch (error) {
        updateStatus('Error: ' + error.message);
        console.error('[Export Error]:', error);
      }
    }

    // Log Electron env for debug
    console.log('[Nativefier Debug]: User Agent:', navigator.userAgent);
    console.log('[Nativefier Debug]: Platform:', navigator.platform);
  </script>

    </body>
</html>