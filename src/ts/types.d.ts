interface SanctuaryTile {
  id: string;
  location: string;
  state: number;
  pId: number;
  extraDatas: any;
  x: number;
  y: number;
}

interface SanctuaryPlayer extends Player {
  energy: number; // any information you add on each result['players']
  hand: SanctuaryTile[]; // only filled for the current player
  handCount: number;
}

interface SanctuaryGamedatas extends Gamedatas<SanctuaryPlayer> {
  tiles: SanctuaryTile[]; // tiles in the pools and on the players boards
}

/*
 * Describe here the types for your state args
 */
interface PlayerTurnArgs {
  playableCardsIds: number[];
}

interface ChooseActionCardArgs {
  strengths: { strength: number; type: string; id: number }[];
}

interface SanctuaryCell {
  x: number;
  y: number;
}

interface PlayAnimalArgs {
  habitat: string;
  level: number;
  sourceName: string | null;
  playableCardsIds: string[];
  playableTiles: { [tileId: string]: SanctuaryCell[] };
  // tileId => locationKey ("x_y") => cells that must be covered by an open area. Empty array when none.
  neededOpenAreas: { [tileId: string]: { [locationKey: string]: SanctuaryCell[] } | [] };
}
/*
 * Describe here the types for your notif args
 */
