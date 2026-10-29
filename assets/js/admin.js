(() => {
  'use strict';
  document.querySelectorAll('.wem-bba-media').forEach((area) => {
    const field = area.querySelector('input[type="hidden"]');
    const preview = area.querySelector('.wem-bba-preview');
    const choose = area.querySelector('.wem-bba-select');
    const remove = area.querySelector('.wem-bba-remove');
    if (!field || !preview || !choose || !remove || !window.wp?.media) return;
    choose.addEventListener('click', () => {
      const frame = wp.media({ title: '选择广告图片', button: { text: '使用图片' }, library: { type: 'image' }, multiple: false });
      frame.on('select', () => {
        const asset = frame.state().get('selection').first().toJSON();
        field.value = asset.id;
        preview.src = asset.sizes?.medium?.url || asset.url;
        preview.style.display = 'block';
      });
      frame.open();
    });
    remove.addEventListener('click', () => {
      field.value = '';
      preview.removeAttribute('src');
      preview.style.display = 'none';
    });
  });
})();
