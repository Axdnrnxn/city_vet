const firstAidVideoLibrary = [];

async function loadFirstAidVideosFromCms() {
    try {
        const response = await fetch('../../api/cms/first_aid_videos.php?public=1');
        const data = await response.json();

        if (data.status !== 'success' || !Array.isArray(data.items)) {
            renderFirstAidVideoGallery([]);
            return [];
        }

        const normalized = data.items
            .filter(item => item && item.Status === 'active')
            .map(item => ({
                title: item.Title,
                description: item.Description || '',
                embedUrl: item.Embed_Url || '',
                tags: (item.Tags || '').split(',').map(tag => tag.trim()).filter(Boolean)
            }));

        firstAidVideoLibrary.splice(0, firstAidVideoLibrary.length, ...normalized);
        renderFirstAidVideoGallery(firstAidVideoLibrary);
        return firstAidVideoLibrary;
    } catch (error) {
        console.error('Failed to load first aid videos from CMS:', error);
        renderFirstAidVideoGallery([]);
        return [];
    }
}

function renderFirstAidVideoGallery(videos = firstAidVideoLibrary) {
    const container = document.getElementById('firstAidVideoGallery');
    const countElement = document.getElementById('firstAidResultsCount');

    if (!container) return;

    if (!videos.length) {
        container.innerHTML = `
            <div class="col-span-full p-6 rounded-2xl border border-dashed border-rose-200 bg-rose-50 text-center text-rose-700">
                <i class="fa-solid fa-magnifying-glass mr-2"></i>
                No emergency videos match that search. Try terms like “bleeding”, “heatstroke”, or “choking”.
            </div>
        `;
        if (countElement) countElement.textContent = '0 videos found';
        return;
    }

    container.innerHTML = videos.map(video => `
        <article class="group overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm hover:shadow-lg transition-all duration-200">
            <div class="aspect-video bg-gray-950">
                <iframe
                    src="${video.embedUrl}"
                    title="${video.title}"
                    class="w-full h-full"
                    loading="lazy"
                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                    allowfullscreen>
                </iframe>
            </div>
            <div class="p-4">
                <div class="flex items-center justify-between gap-2 mb-2">
                    <span class="inline-flex items-center rounded-full bg-teal-50 px-2.5 py-1 text-[11px] font-bold text-[#00796B] uppercase tracking-wide">
                        <i class="fa-solid fa-heart-pulse mr-1"></i> First Aid
                    </span>
                    <span class="text-[11px] font-semibold text-gray-400">Video</span>
                </div>
                <h4 class="text-base font-bold text-gray-800 mb-2">${video.title}</h4>
                <p class="text-sm text-gray-600 leading-relaxed">${video.description}</p>
            </div>
        </article>
    `).join('');

    if (countElement) {
        countElement.textContent = `${videos.length} video${videos.length === 1 ? '' : 's'} found`;
    }
}

function filterFirstAidVideos(searchTerm) {
    const normalized = searchTerm.trim().toLowerCase();

    if (!normalized) {
        return firstAidVideoLibrary;
    }

    return firstAidVideoLibrary.filter(video => {
        const haystack = [
            video.title,
            video.description,
            video.tags.join(' ')
        ].join(' ').toLowerCase();

        return haystack.includes(normalized);
    });
}

function openYouTubeSearch(searchTerm) {
    const term = (searchTerm || '').trim();
    if (!term) return;

    const youtubeSearchUrl = `https://www.youtube.com/results?search_query=${encodeURIComponent(term + ' pet first aid')}`;
    window.open(youtubeSearchUrl, '_blank', 'noopener');
}

async function initFirstAidVideoLibrary() {
    const searchInput = document.getElementById('firstAidSearch');

    if (!searchInput) return;

    await loadFirstAidVideosFromCms();

    searchInput.addEventListener('input', function () {
        const filteredVideos = filterFirstAidVideos(this.value);
        renderFirstAidVideoGallery(filteredVideos);
    });

    searchInput.addEventListener('keydown', function (event) {
        if (event.key === 'Enter') {
            event.preventDefault();
            openYouTubeSearch(this.value);
        }
    });
}

document.addEventListener('DOMContentLoaded', initFirstAidVideoLibrary);
