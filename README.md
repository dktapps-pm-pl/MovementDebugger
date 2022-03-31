# MovementDebugger
Plugin used during PocketMine-MP 4.1 development for debugging player movement

## Features
- Leave a trail of tiny copies of yourself wherever you go, for precise movement debugging
- Logs whenever you start/stop sneaking/swimming/sprinting/gliding/flying
- Logs which `PlayerAuthInputPacket` flags are set, if any

## Wtf?
I used this plugin for debugging during PocketMine-MP 4.1 development to test the transition from `MovePlayerPacket` to `PlayerAuthInputPacket`.
