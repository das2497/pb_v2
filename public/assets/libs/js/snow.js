// Snowfall configuration
const config = {
    snowflakeCount: 120,  // Number of snowflakes
    snowflakes: ['❄', '❅', '❆', '•'], // Different snowflake types
    colors: ['#ffffff', '#e8f4f8', '#9bcfe2ff'], // Snowflake colors
    layers: 4 // Number of depth layers
};

// Create snowflakes
function createSnowfall() {
    const container = document.getElementById('snowContainer');

    for (let i = 0; i < config.snowflakeCount; i++) {
        const snowflake = document.createElement('div');
        snowflake.className = 'snowflake';

        // Random properties for each snowflake
        const size = Math.random() * 1.5 + 0.5;
        const left = Math.random() * 100;
        const layer = Math.floor(Math.random() * config.layers) + 1;
        const type = Math.floor(Math.random() * config.snowflakes.length);
        const color = Math.floor(Math.random() * config.colors.length);

        // Apply properties
        snowflake.innerHTML = config.snowflakes[type];
        snowflake.style.fontSize = size + 'em';
        snowflake.style.left = left + '%';
        snowflake.style.color = config.colors[color];
        snowflake.classList.add('layer' + layer);

        // Random animation delay for more natural effect
        snowflake.style.animationDelay = Math.random() * 15 + 's';

        container.appendChild(snowflake);
    }
}

// Initialize snowfall when page loads
window.addEventListener('load', createSnowfall);

// Optional: Recreate snowflakes on window resize for better responsiveness
window.addEventListener('resize', function () {
    const container = document.getElementById('snowContainer');
    container.innerHTML = '';
    createSnowfall();
});