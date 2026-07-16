const SVG_ICONS = [
  'action-point',
  'active-player',
  'advertise',
  'apprentice',
  'assignment',
  'assistant',
  'coin',
  'component',
  'diamond-ring',
  'die',
  'disk-apprentice',
  'disk-assistant',
  'disk-engineer',
  'disk-magician',
  'disk-manager',
  'disk',
  'end',
  'engineer',
  'fame',
  'fame-threshold',
  'link-shard',
  'link',
  'magician',
  'manager',
  'other-players',
  'performance',
  'perform',
  'shard',
  'special-assignment',
  'spend',
  'trick-marker',
  'trick',
  'spiritual',
  'escape',
  'mechanical',
  'optical',
];
const ICONS = [...SVG_ICONS];

export const formatIcon = function (name: string, n = null, lowerCase = true) {
  let type = lowerCase ? name.toLowerCase() : name;

  const NO_TEXT_ICONS = [];
  let noText = NO_TEXT_ICONS.includes(name);
  let text = n == null ? '' : `<span>${n}</span>`;

  if (SVG_ICONS.includes(type)) {
    let icon = `<i class='svgicon-${type}'>`;
    //   let nGlyphs = glyphs[type];
    //   if (nGlyphs > 1) {
    //     for (let i = 1; i <= nGlyphs; i++) {
    //       icon += `<span class="path${i}"></span>`;
    //     }
    //   }
    icon += '</i>';
    return text + icon;
  }

  return `${noText ? text : ''}<div class="icon-container icon-container-${type}"><div class="nemesis-icon icon-${type}">${noText ? '' : text}</div></div>`;
};

export const formatString = function (str: string) {
  str = str.replaceAll('\n', '<br />');
  ICONS.forEach((name) => {
    str = str.replaceAll(new RegExp('<' + name + '>', 'g'), formatIcon(name));
  });
  str = str.replaceAll(new RegExp('<bullet>', 'g'), '&nbsp;&nbsp;·');
  str = str.replace(/\{\{([^\}]+)\}\}/gi, '<span class="emph">$1</span>'); // Replace {{my wrapped text}} by <span class="emph">my wrapped text</span>

  return str;
};

export const logOverride = {};

export const onLogAdded = {};
