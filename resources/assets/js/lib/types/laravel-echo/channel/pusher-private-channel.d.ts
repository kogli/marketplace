import { PusherChannel } from './pusher-channel';
/**
 * This class represents a Pusher private channel.
 */
export declare class PusherPrivateChannel extends PusherChannel {
    /**
     * Trigger client event on the channel.
     *
     * @param  {Function}  callback
     * @return {PusherPrivateChannel}
     */
    whisper(eventName: any, data: any): PusherPrivateChannel;
}
