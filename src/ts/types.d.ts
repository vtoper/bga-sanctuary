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
  actionCards: SanctuaryActionCard[];
}

interface SanctuaryActionCard {
  id: number;
  strength: number;
  type: string;
  level: number;
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

interface TakeTileArgs {
  n: number;
  inRange: boolean;
  source: string;
  cardIds: string[];
  taken: number;
}

interface ChooseActionCardArgs {
  strengths: { strength: number; type: string; id: number }[];
}

interface SanctuaryCell {
  x: number;
  y: number;
}

interface AnimalArgs {
  habitat: string;
  level: number;
  sourceName: string | null;
  playableCardsIds: string[];
  playableTiles: { [tileId: string]: SanctuaryCell[] };
  // tileId => locationKey ("x_y") => cells that must be covered by an open area. Empty array when none.
  neededOpenAreas: { [tileId: string]: { [locationKey: string]: SanctuaryCell[] } | [] };
}

interface BuildingArgs {
  sourceName: string | null;
  playableCardsIds: string[];
  playableTiles: { [tileId: string]: SanctuaryCell[] };
}

interface ProjectArgs {
  sourceName: string | null;
  level: number;
  playableCardsIds: string[];
  playableTiles: { [tileId: string]: SanctuaryCell[] };
}

interface ConservationMarkerChoice {
  type: string;
  strength: number;
  achievements: string[];
  conservationMarkers?: { [achievement: string]: number };
}

interface ConservationArgs {
  source: string | null;
  playableMarkers: { [markerId: string]: ConservationMarkerChoice };
}

interface AdministrationArgs {
  cardIds: string[];
  discardCount: number;
}

interface HunterArgs {
  n: number;
  cardIds: string[];
}

interface PlaceOpenAreasArgs {
  n: number;
  locations: SanctuaryCell[];
}

interface UpgradeChoice {
  type: string;
  actionCards: number[];
}

interface UpgradeArgs {
  source: string | null;
  playableUpgrades: { [tokenId: string]: UpgradeChoice };
  actionCards: { id: number; type: string }[];
}

/*
 * Describe here the types for your notif args
 */
