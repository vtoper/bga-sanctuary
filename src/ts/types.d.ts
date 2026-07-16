interface SanctuaryPlayer extends Player {
  energy: number; // any information you add on each result['players']
}

interface SanctuaryGamedatas extends Gamedatas<SanctuaryPlayer> {
  // Add here variables you set up in getAllDatas
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
/*
 * Describe here the types for your notif args
 */
