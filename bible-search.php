<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bible Concordance</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            height: 100vh;
            height: 100dvh;
            overflow: hidden;
            background: #f5f5f5;
        }

        .container {
            display: flex;
            height: 100vh;
            height: 100dvh;
            overflow-x: auto;
            scroll-snap-type: x mandatory;
            -webkit-overflow-scrolling: touch;
        }

        @media (min-width: 769px) {
            .container {
                overflow-x: hidden;
                scroll-snap-type: none;
            }
        }

        @media (max-width: 768px) {
            .container {
                scroll-behavior: smooth;
            }
        }

        .left-panel {
            flex: 0 0 50%;
            display: flex;
            flex-direction: column;
            background: white;
            border-right: 1px solid #ddd;
            scroll-snap-align: start;
        }

        @media (max-width: 768px) {
            .left-panel {
                flex: 0 0 100%;
                min-width: 100vw;
            }
        }

        .search-controls {
            position: sticky;
            top: 0;
            z-index: 10;
            padding: 20px;
            border-bottom: 1px solid #ddd;
            background: #fafafa;
        }

        #searchBox {
            width: 100%;
            padding: 12px;
            font-size: 16px;
            border: 2px solid #ddd;
            border-radius: 6px;
            margin-bottom: 12px;
            outline: none;
            transition: border-color 0.2s;
        }

        #searchBox:focus {
            border-color: #4a90e2;
        }

        .radio-group {
            display: flex;
            gap: 0;
            border-radius: 6px;
            overflow: hidden;
            border: 1px solid #ddd;
            width: fit-content;
        }

        .radio-group label {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 8px 20px;
            cursor: pointer;
            font-size: 14px;
            background: white;
            border-right: 1px solid #ddd;
            transition: all 0.2s;
            user-select: none;
        }

        .radio-group label:last-child {
            border-right: none;
        }

        .radio-group label:hover {
            background: #f5f5f5;
        }

        .radio-group input[type="radio"] {
            display: none;
        }

        .radio-group input[type="radio"]:checked + span {
            background: #4a90e2;
            color: white;
            font-weight: 600;
        }
        
        .radio-group label:has(input[type="radio"]:checked) {
            background: #4a90e2;
            color: white;
        }

        .radio-group label > span {
            padding: 0;
            transition: all 0.2s;
        }

        .results-list {
            flex: 1;
            overflow-y: auto;
        }

        .result-item {
            padding: 12px 20px;
            border-bottom: 1px solid #f0f0f0;
            cursor: pointer;
            transition: background 0.2s;
        }

        .result-item:hover {
            background: #e8f4f8;
        }

        .result-item.active {
            background: #d4e9f7;
            border-left: 4px solid #4a90e2;
            padding-left: 16px;
        }

        .highlight {
            background: #ffeb3b;
            font-weight: 600;
        }

        .right-panel {
            flex: 0 0 50%;
            background: white;
            overflow-y: auto;
            padding: 20px 30px;
            scroll-snap-align: start;
        }

        @media (max-width: 768px) {
            .right-panel {
                flex: 0 0 100%;
                min-width: 100vw;
            }
        }

        .verse {
            margin-bottom: 4px;
            line-height: 1.7;
            color: #333;
        }

        .verse.focused {
            background: #fff9c4;
            margin-left: -8px;
            padding-left: 8px;
            border-left: 3px solid #fbc02d;
        }

        .no-results {
            text-align: center;
            padding: 40px 20px;
            color: #999;
        }

        .loading {
            text-align: center;
            padding: 20px;
            color: #666;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="left-panel">
            <div class="search-controls">
                <input type="text" id="searchBox" placeholder="Search for words or phrases..." autocomplete="off">
                <div class="radio-group">
                    <label>
                        <input type="radio" name="source" value="kjv" checked>
                        <span>King James Version</span>
                    </label>
                    <label>
                        <input type="radio" name="source" value="web">
                        <span>World English Bible</span>
                    </label>
                </div>
            </div>
            <div class="results-list" id="resultsList"></div>
        </div>
        <div class="right-panel" id="versePanel"></div>
    </div>

    <script>
        <?php
        // Read the files and embed as JavaScript variables
        $kjvContent = file_exists('kjv.txt') ? file_get_contents('kjv.txt') : '';
        $webContent = file_exists('web.txt') ? file_get_contents('web.txt') : '';
        
        echo "const kjvData = " . json_encode($kjvContent) . ";\n";
        echo "const webData = " . json_encode($webContent) . ";\n";
        ?>

        // Parse the data into verses
        function parseVerses(data) {
            const lines = data.split('\n').filter(line => line.trim());
            return lines.map((line, idx) => ({
                id: idx,
                text: line
            }));
        }

        const kjvVerses = parseVerses(kjvData);
        const webVerses = parseVerses(webData);

        let currentSource = 'kjv';
        let currentVerses = kjvVerses;
        let focusedVerseId = null;
        let displayedVerseRange = { start: 0, end: 400 };

        // Search state
        let searchResults = [];
        let displayedSearchRange = { start: 0, end: 100 };
        let isSearching = false;
        let searchAbortController = null;
        let currentSearchTerm = '';
        
        // Loading locks
        let isLoadingVerses = false;
        let isLoadingSearchResults = false;
        let isAutoScrolling = false;

        // Word-boundary search: match all words in any order
        function searchWithScore(text, pattern) {
            if (!pattern) return { match: true, indices: [], score: 0 };
            
            const lowerText = text.toLowerCase();
            const lowerPattern = pattern.toLowerCase();
            
            // Split pattern into words
            const patternWords = lowerPattern.split(/\s+/).filter(w => w.length > 0);
            if (patternWords.length === 0) return { match: true, indices: [], score: 0 };
            
            const textWords = lowerText.split(/\s+/);
            
            let allWordsFound = true;
            let wordIndices = [];
            let wordPositions = [];
            
            for (const patternWord of patternWords) {
                let found = false;
                for (let i = 0; i < textWords.length; i++) {
                    if (textWords[i].includes(patternWord)) {
                        found = true;
                        wordPositions.push(i);
                        // Find character indices for this word
                        let charPos = 0;
                        for (let j = 0; j < i; j++) {
                            charPos += textWords[j].length + 1; // +1 for space
                        }
                        const wordStart = textWords[i].indexOf(patternWord);
                        for (let k = 0; k < patternWord.length; k++) {
                            wordIndices.push(charPos + wordStart + k);
                        }
                        break;
                    }
                }
                if (!found) {
                    allWordsFound = false;
                    break;
                }
            }
            
            if (!allWordsFound) {
                return { match: false, indices: [], score: 0 };
            }
            
            // Sort and deduplicate indices
            wordIndices.sort((a, b) => a - b);
            wordIndices = [...new Set(wordIndices)];
            
            let score = 500; // Base score
            
            // Bonus for exact substring match
            if (lowerText.includes(lowerPattern)) {
                score += 500;
                // Extra bonus if at start
                if (lowerText.indexOf(lowerPattern) === 0) {
                    score += 200;
                }
            }
            
            // Bonus for words being close together
            if (wordPositions.length > 1) {
                const spread = Math.max(...wordPositions) - Math.min(...wordPositions);
                score += Math.max(0, 200 - spread * 20);
            }
            
            // Bonus for words in order
            let inOrder = true;
            for (let i = 1; i < wordPositions.length; i++) {
                if (wordPositions[i] < wordPositions[i - 1]) {
                    inOrder = false;
                    break;
                }
            }
            if (inOrder) score += 150;
            
            // Bonus for matching at start of verse
            if (wordPositions[0] === 0) score += 100;
            
            // Bonus for shorter text (more focused match)
            score += Math.max(0, 100 - lowerText.length / 10);
            
            return { match: true, indices: wordIndices, score };
        }

        function highlightText(text, indices) {
            if (!indices || indices.length === 0) return escapeHtml(text);
            
            let result = '';
            let lastIdx = 0;
            
            for (let idx of indices) {
                result += escapeHtml(text.substring(lastIdx, idx));
                result += `<span class="highlight">${escapeHtml(text[idx])}</span>`;
                lastIdx = idx + 1;
            }
            result += escapeHtml(text.substring(lastIdx));
            
            return result;
        }
        
        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

        // Progressive search that yields control back to the browser
        async function performProgressiveSearch(verses, searchTerm) {
            const results = [];
            const FRAME_BUDGET_MS = 8; // Leave time for 60fps rendering
            let startTime = performance.now();
            
            for (let i = 0; i < verses.length; i++) {
                // Check if we should yield
                if (performance.now() - startTime > FRAME_BUDGET_MS) {
                    await new Promise(resolve => requestAnimationFrame(resolve));
                    startTime = performance.now();
                    
                    // Check if search was cancelled
                    if (currentSearchTerm !== searchTerm) {
                        return null; // Search was superseded
                    }
                }
                
                const verse = verses[i];
                const result = searchWithScore(verse.text, searchTerm);
                if (result.match) {
                    results.push({ ...verse, ...result });
                }
            }
            
            // Sort by score (descending)
            results.sort((a, b) => b.score - a.score);
            
            return results;
        }
        
        async function updateResults() {
            const searchTerm = document.getElementById('searchBox').value;
            const resultsContainer = document.getElementById('resultsList');
            
            currentSearchTerm = searchTerm;
            
            if (!searchTerm.trim()) {
                resultsContainer.innerHTML = '<div class="no-results">Start typing to search...</div>';
                searchResults = [];
                isSearching = false;
                return;
            }
            
            // Show loading indicator
            resultsContainer.innerHTML = '<div class="loading">Searching...</div>';
            
            // Perform progressive search
            const results = await performProgressiveSearch(currentVerses, searchTerm);
            
            // Check if this search is still relevant
            if (results === null || currentSearchTerm !== searchTerm) {
                return; // Search was cancelled or superseded
            }
            
            searchResults = results;
            isSearching = true;
            displayedSearchRange = { start: 0, end: 100 };
            renderSearchResults();
        }

        function renderSearchResults(append = false) {
            const resultsContainer = document.getElementById('resultsList');
            
            if (searchResults.length === 0) {
                resultsContainer.innerHTML = '<div class="no-results">No results found</div>';
                return;
            }
            
            const { start, end } = displayedSearchRange;
            
            if (!append) {
                // Initial render with explicit DOM construction
                const fragment = document.createDocumentFragment();
                const resultsToShow = searchResults.slice(start, end);
                
                resultsToShow.forEach(verse => {
                    const div = document.createElement('div');
                    div.className = `result-item ${verse.id === focusedVerseId ? 'active' : ''}`;
                    div.dataset.id = verse.id;
                    div.innerHTML = highlightText(verse.text, verse.indices);
                    fragment.appendChild(div);
                });
                
                resultsContainer.innerHTML = '';
                resultsContainer.appendChild(fragment);
                
                if (end < searchResults.length) {
                    const loadingDiv = document.createElement('div');
                    loadingDiv.className = 'loading';
                    loadingDiv.textContent = 'Scroll for more results...';
                    resultsContainer.appendChild(loadingDiv);
                }
            } else {
                // Append more results
                const loadingIndicator = resultsContainer.querySelector('.loading');
                if (loadingIndicator) {
                    loadingIndicator.remove();
                }
                
                const oldEnd = end - 50;
                const resultsToShow = searchResults.slice(oldEnd, end);
                
                const fragment = document.createDocumentFragment();
                resultsToShow.forEach(verse => {
                    const div = document.createElement('div');
                    div.className = `result-item ${verse.id === focusedVerseId ? 'active' : ''}`;
                    div.dataset.id = verse.id;
                    div.innerHTML = highlightText(verse.text, verse.indices);
                    fragment.appendChild(div);
                });
                
                resultsContainer.appendChild(fragment);
                
                if (end < searchResults.length) {
                    const loadingDiv = document.createElement('div');
                    loadingDiv.className = 'loading';
                    loadingDiv.textContent = 'Scroll for more results...';
                    resultsContainer.appendChild(loadingDiv);
                }
            }
        }

        function handleSearchScroll() {
            if (isLoadingSearchResults) return;
            
            const container = document.getElementById('resultsList');
            const scrollTop = container.scrollTop;
            const scrollHeight = container.scrollHeight;
            const clientHeight = container.clientHeight;
            
            // Use 40% of viewport height as trigger distance
            const triggerDistance = clientHeight * 0.4;
            
            // Load more when within trigger distance of bottom
            if (scrollTop + clientHeight >= scrollHeight - triggerDistance) {
                if (displayedSearchRange.end < searchResults.length) {
                    isLoadingSearchResults = true;
                    displayedSearchRange.end = Math.min(searchResults.length, displayedSearchRange.end + 50);
                    renderSearchResults(true);
                    isLoadingSearchResults = false;
                }
            }
        }

        function renderVersePanel(mode = 'replace') {
            const panel = document.getElementById('versePanel');
            const { start, end } = displayedVerseRange;
            
            if (mode === 'replace') {
                // Full replace with explicit DOM construction
                const fragment = document.createDocumentFragment();
                const versesToRender = currentVerses.slice(start, end);
                
                versesToRender.forEach(verse => {
                    const div = document.createElement('div');
                    div.className = `verse ${verse.id === focusedVerseId ? 'focused' : ''}`;
                    div.dataset.id = verse.id;
                    div.textContent = verse.text;
                    fragment.appendChild(div);
                });
                
                panel.innerHTML = '';
                panel.appendChild(fragment);
            } else if (mode === 'append') {
                // Append to bottom
                const oldEnd = end - 50;
                const versesToAdd = currentVerses.slice(oldEnd, end);
                
                const fragment = document.createDocumentFragment();
                versesToAdd.forEach(verse => {
                    const div = document.createElement('div');
                    div.className = `verse ${verse.id === focusedVerseId ? 'focused' : ''}`;
                    div.dataset.id = verse.id;
                    div.textContent = verse.text;
                    fragment.appendChild(div);
                });
                
                panel.appendChild(fragment);
            } else if (mode === 'prepend') {
                // Prepend to top
                const newStart = start;
                const oldStart = start + 50;
                const versesToAdd = currentVerses.slice(newStart, oldStart);
                
                const fragment = document.createDocumentFragment();
                versesToAdd.forEach(verse => {
                    const div = document.createElement('div');
                    div.className = `verse ${verse.id === focusedVerseId ? 'focused' : ''}`;
                    div.dataset.id = verse.id;
                    div.textContent = verse.text;
                    fragment.appendChild(div);
                });
                
                panel.insertBefore(fragment, panel.firstChild);
            }
        }

        function focusVerse(verseId) {
            focusedVerseId = verseId;
            
            // Adjust displayed range to include the focused verse, centered at 50%
            displayedVerseRange.start = Math.max(0, verseId - 200);
            displayedVerseRange.end = Math.min(currentVerses.length, displayedVerseRange.start + 400);
            
            renderVersePanel();
            
            // On mobile, scroll to the right panel
            if (window.innerWidth <= 768) {
                const container = document.querySelector('.container');
                const rightPanel = document.getElementById('versePanel');
                container.scrollLeft = rightPanel.offsetLeft;
            }
            
            // Set auto-scrolling flag to prevent interference
            isAutoScrolling = true;
            
            // Scroll to the focused verse
            requestAnimationFrame(() => {
                const focusedElement = document.querySelector('.verse.focused');
                if (focusedElement) {
                    focusedElement.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    
                    // Clear the flag after scroll completes (smooth scroll takes ~500-1000ms)
                    setTimeout(() => {
                        isAutoScrolling = false;
                    }, 1000);
                }
            });
            
            // Update active state in results
            if (isSearching) {
                renderSearchResults();
            }
        }

        function handleVerseScroll() {
            if (isLoadingVerses || isAutoScrolling) return;
            
            const panel = document.getElementById('versePanel');
            const scrollTop = panel.scrollTop;
            const scrollHeight = panel.scrollHeight;
            const clientHeight = panel.clientHeight;
            
            // Calculate scroll position as percentage of total scrollable area
            const maxScroll = scrollHeight - clientHeight;
            const scrollPercent = scrollTop / maxScroll;
            
            // If within 20% of either end, reposition to center (50%)
            if (scrollPercent < 0.2 && displayedVerseRange.start > 0) {
                isLoadingVerses = true;
                
                // Calculate how many verses to shift to get back to 50%
                const currentVerseIndex = displayedVerseRange.start + Math.floor((displayedVerseRange.end - displayedVerseRange.start) * scrollPercent);
                const targetStart = Math.max(0, currentVerseIndex - 200);
                const targetEnd = Math.min(currentVerses.length, targetStart + 400);
                
                displayedVerseRange.start = targetStart;
                displayedVerseRange.end = targetEnd;
                
                renderVersePanel('replace');
                
                // Position scroll at 50% of the new range
                requestAnimationFrame(() => {
                    const newMaxScroll = panel.scrollHeight - panel.clientHeight;
                    panel.scrollTop = newMaxScroll * 0.5;
                    isLoadingVerses = false;
                });
            } else if (scrollPercent > 0.8 && displayedVerseRange.end < currentVerses.length) {
                isLoadingVerses = true;
                
                // Calculate how many verses to shift to get back to 50%
                const currentVerseIndex = displayedVerseRange.start + Math.floor((displayedVerseRange.end - displayedVerseRange.start) * scrollPercent);
                const targetStart = Math.max(0, currentVerseIndex - 200);
                const targetEnd = Math.min(currentVerses.length, targetStart + 400);
                
                displayedVerseRange.start = targetStart;
                displayedVerseRange.end = targetEnd;
                
                renderVersePanel('replace');
                
                // Position scroll at 50% of the new range
                requestAnimationFrame(() => {
                    const newMaxScroll = panel.scrollHeight - panel.clientHeight;
                    panel.scrollTop = newMaxScroll * 0.5;
                    isLoadingVerses = false;
                });
            }
        }

        // Event listeners
        document.getElementById('searchBox').addEventListener('input', updateResults);

        document.querySelectorAll('input[name="source"]').forEach(radio => {
            radio.addEventListener('change', (e) => {
                currentSource = e.target.value;
                currentVerses = currentSource === 'kjv' ? kjvVerses : webVerses;
                displayedVerseRange = { start: 0, end: 400 };
                focusedVerseId = null;
                updateResults();
                renderVersePanel();
            });
        });

        document.getElementById('resultsList').addEventListener('click', (e) => {
            const item = e.target.closest('.result-item');
            if (item) {
                const verseId = parseInt(item.dataset.id);
                focusVerse(verseId);
            }
        });

        document.getElementById('resultsList').addEventListener('scroll', handleSearchScroll);
        document.getElementById('versePanel').addEventListener('scroll', handleVerseScroll);

        // Initialize
        renderVersePanel();
        document.getElementById('resultsList').innerHTML = '<div class="no-results">Start typing to search...</div>';
    </script>
</body>
</html>
