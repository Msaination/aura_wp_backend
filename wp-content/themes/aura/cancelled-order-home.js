document.addEventListener("click", async (event) => {
  const target = event.target instanceof Element ? event.target : null;
  const homeLink = target?.closest("[data-aura-clear-cache]");

  if (!(homeLink instanceof HTMLAnchorElement)) {
    return;
  }

  event.preventDefault();
  const destination = new URL(homeLink.href);
  destination.searchParams.set("_aura_cache_bust", String(Date.now()));

  try {
    if ("caches" in window) {
      const cacheNames = await caches.keys();
      await Promise.all(cacheNames.map((cacheName) => caches.delete(cacheName)));
    }
  } catch {
    // Continue navigation if Cache Storage is unavailable.
  }

  window.location.assign(destination.toString());
});
