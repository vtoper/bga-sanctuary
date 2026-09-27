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
  energy: number;
  hand: SanctuaryTile[]; // only filled for the current player
  handCount: number;
  actionCards: SanctuaryActionCard[];
  pouch: number;
  icons: { [iconName: string]: number }; // countCardIcons() — 16 continent/type/habitat counts
}

interface SanctuaryActionCard {
  id: number;
  strength: number; // slot the card sits in, 1 to 4
  type: string; // Rock | Forest | Project | Water — drives the actionCards.jpg sprite column
  level: number; // 1 or 2, upgraded cards act one slot further
  status: number; // 1 while the card is the one chosen for the current turn
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

interface PouchArgs {
  n: number;
  cardIds: string[];
}

interface MoveActionCardArgs {
  actionCards: SanctuaryActionCard[];
}

interface RelocateArgs {
  playableCardsIds: string[];
  playableTiles: { [tileId: string]: SanctuaryCell[] };
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
