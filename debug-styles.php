<?php
/**
 * TEMPORARY DEBUG FILE
 * Shows CSS load order and computed styles
 * 
 * ACCESS: http://localhost:10010/?debug_styles=1
 */

if (!isset($_GET['debug_styles'])) {
    return;
}

get_header();
?>

<style>
.debug-panel {
    position: fixed;
    top: 100px;
    right: 20px;
    background: white;
    border: 3px solid #e53935;
    border-radius: 8px;
    padding: 20px;
    max-width: 400px;
    max-height: 80vh;
    overflow-y: auto;
    z-index: 99999;
    box-shadow: 0 8px 24px rgba(0,0,0,0.3);
    font-size: 12px;
    font-family: monospace;
}

.debug-panel h2 {
    margin: 0 0 15px;
    color: #e53935;
    font-size: 16px;
}

.debug-section {
    margin-bottom: 20px;
    padding-bottom: 15px;
    border-bottom: 1px solid #eee;
}

.debug-label {
    font-weight: bold;
    color: #333;
    margin-bottom: 5px;
}

.debug-value {
    background: #f5f5f5;
    padding: 8px;
    border-radius: 4px;
    word-break: break-all;
    margin-bottom: 5px;
}

.debug-error {
    color: #e53935;
    font-weight: bold;
}

.debug-success {
    color: #4caf50;
    font-weight: bold;
}

.stores-grid-cd {
    border: 5px dashed #e53935 !important;
}
</style>

<div class="debug-panel">
    <h2>🔍 CSS DEBUG PANEL</h2>
    
    <!-- Current Grid Columns -->
    <div class="debug-section">
        <div class="debug-label">Current Grid Columns:</div>
        <div class="debug-value" id="debug-grid-columns">Loading...</div>
    </div>
    
    <!-- Expected vs Actual -->
    <div class="debug-section">
        <div class="debug-label">Expected: 6 columns</div>
        <div class="debug-label">Actual: <span id="debug-column-count">?</span></div>
    </div>
    
    <!-- CSS Files Loaded -->
    <div class="debug-section">
        <div class="debug-label">CSS Files Loading Order:</div>
        <div id="debug-css-files"></div>
    </div>
    
    <!-- Grid Element Info -->
    <div class="debug-section">
        <div class="debug-label">Grid Element Found:</div>
        <div class="debug-value" id="debug-element-found">Checking...</div>
    </div>
    
    <!-- Conflicting Styles -->
    <div class="debug-section">
        <div class="debug-label">Conflicting Styles:</div>
        <div id="debug-conflicts"></div>
    </div>
    
    <!-- Fix Button -->
    <div class="debug-section">
        <button onclick="applyQuickFix()" style="background:#4caf50;color:white;padding:10px 20px;border:none;border-radius:4px;cursor:pointer;width:100%;font-weight:bold;">
            ⚡ APPLY QUICK FIX
        </button>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Check grid element
    const grid = document.querySelector('.stores-grid-cd');
    const foundEl = document.getElementById('debug-element-found');
    
    if (!grid) {
        foundEl.innerHTML = '<span class="debug-error">❌ NOT FOUND!</span>';
        return;
    }
    
    foundEl.innerHTML = '<span class="debug-success">✅ Found</span>';
    
    // Get computed style
    const computedStyle = window.getComputedStyle(grid);
    const gridColumns = computedStyle.gridTemplateColumns;
    
    document.getElementById('debug-grid-columns').textContent = gridColumns;
    
    // Count columns
    const columnCount = gridColumns.split(' ').length;
    const countEl = document.getElementById('debug-column-count');
    countEl.textContent = columnCount;
    countEl.className = columnCount === 6 ? 'debug-success' : 'debug-error';
    
    // List CSS files
    const cssFilesEl = document.getElementById('debug-css-files');
    let cssHTML = '';
    let index = 1;
    
    Array.from(document.styleSheets).forEach(sheet => {
        try {
            const url = sheet.href || 'inline styles';
            const fileName = url.split('/').pop();
            cssHTML += `<div class="debug-value">${index}. ${fileName}</div>`;
            index++;
        } catch(e) {
            cssHTML += `<div class="debug-value">${index}. [Cross-origin stylesheet]</div>`;
            index++;
        }
    });
    
    cssFilesEl.innerHTML = cssHTML;
    
    // Check for conflicts
    const conflictsEl = document.getElementById('debug-conflicts');
    let conflicts = [];
    
    // Check if responsive.css is overriding
    const allRules = [];
    Array.from(document.styleSheets).forEach(sheet => {
        try {
            Array.from(sheet.cssRules || sheet.rules || []).forEach(rule => {
                if (rule.selectorText && rule.selectorText.includes('stores-grid-cd')) {
                    allRules.push({
                        selector: rule.selectorText,
                        file: sheet.href ? sheet.href.split('/').pop() : 'inline',
                        gridColumns: rule.style.gridTemplateColumns || 'not set'
                    });
                }
            });
        } catch(e) {}
    });
    
    if (allRules.length > 0) {
        let conflictHTML = '';
        allRules.forEach(rule => {
            conflictHTML += `<div class="debug-value">
                <strong>${rule.file}</strong><br>
                ${rule.selector}<br>
                columns: ${rule.gridColumns}
            </div>`;
        });
        conflictsEl.innerHTML = conflictHTML;
    } else {
        conflictsEl.innerHTML = '<div class="debug-value">No rules found</div>';
    }
});

// Quick fix function
function applyQuickFix() {
    const grid = document.querySelector('.stores-grid-cd');
    if (grid) {
        grid.style.cssText = 'display: grid !important; grid-template-columns: repeat(6, 1fr) !important; gap: 16px !important;';
        alert('✅ Quick fix applied! Grid should now show 6 columns. This is temporary - check the debug panel for the real issue.');
        location.reload();
    } else {
        alert('❌ Grid element not found!');
    }
}
</script>

<?php
// Show actual homepage content
get_template_part('index');

get_footer();
