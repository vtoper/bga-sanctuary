import { bga } from '../framework/utils';
import { ActionCardNotifications } from './ActionCardNotifications';
import { FrameworkNotifications } from './FrameworkNotifications';
import { PlayerNotifications } from './PlayerNotifications';
import { TilesNotifications } from './TilesNotifications';

export default [
  new TilesNotifications(bga),
  new ActionCardNotifications(bga),
  new PlayerNotifications(bga),
  new FrameworkNotifications(bga),
];
