const https = require('https');
const movies = ['Man of Steel', 'Wonder Woman', 'X-Men', 'X-Men: Days of Future Past'];

movies.forEach(movie => {
  https.get(`https://itunes.apple.com/search?term=${encodeURIComponent(movie)}&entity=movie&limit=1`, res => {
    let data = '';
    res.on('data', chunk => data += chunk);
    res.on('end', () => {
      try {
        const result = JSON.parse(data);
        if (result.results && result.results.length > 0) {
          const url = result.results[0].artworkUrl100.replace('100x100bb', '600x900bb');
          console.log(movie + ': ' + url);
        } else {
          console.log(movie + ': Not found');
        }
      } catch (e) { console.error('Error parsing: ', movie); }
    });
  });
});
