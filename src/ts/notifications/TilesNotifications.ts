export class TilesNotifications {
  bga: ExtendedBga;

  constructor(bga: ExtendedBga) {
    this.bga = bga;
  }

  async notif_fillPool(args) {
    console.debug(args);
  }
}
